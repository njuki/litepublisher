<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcron extends tevents {
  public static $pinged = false;
  public $disableadd;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'cron';
    $this->addevents('added', 'deleted');
    $this->data['password'] = '';
    $this->data['path'] = '';
    $this->cache = false;
    $this->disableadd = false;
    $this->table = 'cron';
  }
  
  protected function geturl() {
    return '/croncron.htm' . litepublisher::$site->q . "cronpass=$this->password";
  }
  
  public function getlockpath() {
    if ($result = $this->path) {
      if (is_dir($result)) return $result;
    }
    return litepublisher::$paths->data;
  }
  
  public function request($arg) {
    if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) return 403;
    if (($fh = @fopen($this->lockpath .'cron.lok', 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
      try {
        set_time_limit(300);
        if (litepublisher::$debug) {
          ignore_user_abort(true);
        } else {
          litepublisher::$urlmap->close_connection();
        }
        if (ob_get_level()) ob_end_flush ();
        flush();
        
        $this->sendexceptions();
        $this->log("started loop");
        $this->execute();
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
      flock($fh, LOCK_UN);
      fclose($fh);
      @chmod($this->lockpath .'cron.lok', 0666);
      $this->log("finished loop");
      return 'Ok';
    }
    return 'locked';
  }
  
  protected function execute() {
    echo "<pre>\n";
    while ($item = $this->db->getassoc(sprintf("date <= '%s' order by date asc limit 1", sqldate()))) {
      //sleep(2);
      extract($item);
  $this->log("task started:\n{$class}->{$func}($arg)");
      $arg = unserialize($arg);
      if ($class == '' ) {
        if (function_exists($func)) {
          try {
            $func($arg);
          } catch (Exception $e) {
            litepublisher::$options->handexception($e);
          }
        } else {
          $this->db->iddelete($id);
          continue;
        }
      } elseif (class_exists($class)) {
        try {
          $obj = getinstance($class);
          $obj->$func($arg);
        } catch (Exception $e) {
          litepublisher::$options->handexception($e);
        }
      } else {
        $this->db->iddelete($id);
        continue;
      }
      if ($type == 'single') {
        $this->db->iddelete($id);
      } else {
        $date = sqldate(strtotime("+1 $type"));
        $this->db->setvalue($id, 'date', $date);
      }
    }
  }
  
  public function add($type, $class, $func, $arg = null) {
    if (!preg_match('/^single|hour|day|week$/', $type)) $this->error("Unknown cron type $type");
    if ($this->disableadd) return false;
    $id = $this->doadd($type, $class, $func, $arg);
    if (($type == 'single') && !self::$pinged) {
      if (tfilestorage::$memcache) {
        tfiler::log("cron added $id");
        $memcache = tfilestorage::$memcache;
        $k =litepublisher::$domain . ':lastpinged';
        $lastpinged = $memcache->get($k);
        if ($lastpinged && (time() > $lastpinged + 300)) {
          self::pingonshutdown();
        } else {
          $k =litepublisher::$domain . ':singlepinged';
          $singlepinged = $memcache->get($k);
          if (!$singlepinged) {
            $memcache->set($k, time(), false, 3600);
          } elseif (time() > $singlepinged  + 300) {
            self::pingonshutdown();
          }
        }
      } else {
        self::pingonshutdown();
      }
    }
    
    return $id;
  }
  
  protected function doadd($type, $class, $func, $arg ) {
    $id = $this->db->add(array(
    'date' => sqldate(),
    'type' => $type,
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function addnightly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'day',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    $this->added($id);
    return $id;
  }
  
  public function addweekly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'week',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->deleted($id);
  }
  
  public function deleteclass($c) {
    $class = self::get_class_name($c);
    $this->db->delete("class = '$class'");
  }
  
  public static function pingonshutdown() {
    if (self::$pinged) return;
    self::$pinged = true;
    
    if (tfilestorage::$memcache) {
      $memcache = tfilestorage::$memcache;
      $k =litepublisher::$domain . ':lastpinged';
      $memcache->set($k, time(), false, 3600);
      $k =litepublisher::$domain . ':singlepinged';
      $memcache->delete($k);
    }
    
    register_shutdown_function(array(tcron::i(), 'ping'));
  }
  
  public function ping() {
    $p = parse_url(litepublisher::$site->url . $this->url);
    $this->pinghost($p['host'], $p['path'] . (empty($p['query']) ? '' : '?' . $p['query']));
  }
  
  private function pinghost($host, $path) {
    //$this->log("pinged host $host$path");
    if (		$socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs( $socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
    }
  }
  
  public function sendexceptions() {
    $filename = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
    if (!file_exists($filename)) return;
    $time = @filectime ($filename);
    if (($time === false) || ($time + 3600 > time())) return;
    $s = file_get_contents($filename);
    tfilestorage::delete($filename);
    tmailer::SendAttachmentToAdmin('[error] '. litepublisher::$site->name, 'See attachment', 'errors.txt', $s);
    sleep(2);
  }
  
  public function log($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    if (litepublisher::$debug) tfiler::log($s, 'cron.log');
  }
  
}//class