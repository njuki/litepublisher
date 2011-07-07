<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tabstractcron extends tevents {
  public static $pinged = false;
  public $disableadd;
  
  protected function create() {
    parent::create();
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . 'index';
    $this->addevents('added', 'deleted');
    $this->data['password'] = '';
    $this->data['path'] = '';
    $this->cache = false;
    $this->disableadd = false;
  }
  
  protected function getdir() {
    return litepublisher::$paths->data . 'cron' . DIRECTORY_SEPARATOR;
  }
  
  protected function getpath() {
    if (($this->data['path'] != '') && is_dir($this->data['path'])) {
      return  $this->data['path'];
    }
    return  $this->getdir();
  }
  
  protected function geturl() {
    return "/croncron.htm" . litepublisher::$site->q . "cronpass=$this->password";
  }
  
  public function request($arg) {
    if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) return 403;
    if (($fh = @fopen($this->path .'cron.lok', 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
      try {
        ignore_user_abort(true);
        set_time_limit(300);
        $this->sendexceptions();
        $this->log("started loop");
        $this->execute();
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
      flock($fh, LOCK_UN);
      fclose($fh);
      @chmod($this->path .'cron.lok', 0666);
      $this->log("finished loop");
      return 'Ok';
    }
    return 'locked';
  }
  
  protected function execute() {
    $this->error('call abstract method execute');
  }
  
  protected function doadd($type, $class, $func, $arg)  {
    $this->error('call abstract method doadd');
  }
  
  public function add($type, $class, $func, $arg = null) {
    if (!preg_match('/^single|hour|day|week$/', $type)) $this->error("Unknown cron type $type");
    if ($this->disableadd) return false;
    $id = $this->doadd($type, $class, $func, $arg);
    if (($type == 'single') && !self::$pinged) self::pingonshutdown();
    return $id;
  }
  
  public static function pingonshutdown() {
    if (self::$pinged) return;
    self::$pinged = true;
    register_shutdown_function(array(tcron::instance(), 'ping'));
  }
  
  public function ping() {
    $this->pinghost(litepublisher::$urlmap->host, litepublisher::$site->subdir . $this->url);
  }
  
  private function pinghost($host, $path) {
    //$this->log("pinged host $host$path");
    if (		$socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs( $socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
    }
  }
  
  public function sendexceptions() {
    //проверить, если файл логов создан более часа назад, то его отослать на почту
    $filename = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
    if (!file_exists($filename)) return;
    $time = @filectime ($filename);
    if (($time === false) || ($time + 3600 > time())) return;
    $s = file_get_contents($filename);
    @unlink($filename);
    tmailer::SendAttachmentToAdmin('[error] '. litepublisher::$site->name, 'See attachment', 'errors.txt', $s);
    sleep(2);
  }
  
  public function log($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    if (litepublisher::$debug) tfiler::log($s, 'cron.log');
  }
  
}//class

?>