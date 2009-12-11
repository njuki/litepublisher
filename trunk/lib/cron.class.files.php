<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcrontask extends tdata {
  public $owner;
  
  public function __construct(&$owner) {
    $this->owner = $owner;
    parent::__construct();
  }
  
  protected function create() {
    $this->data= array(
    'id' => 0,
    'type' => 'single',
    'time' => 0,
    'class' => '',
    'func' => '',
    'arg' => ''
    );
  }
  
  public function Getclass() {
    return $this->data['class'];
  }
  
  public function add($id, $type, $class, $func, $arg) {
    if (!in_array($type, array('single', 'hour', 'day', 'week'))) return $this->Error("unknown cron task $type");
    $this->id = $id;
    $this->type= $type;
    $this->class = $class;
    $this->func = $func;
    $this->arg = $arg;
    $this->time = $this->GetExpired();
    $this->Save();
  }
  
  public function getexpired() {
    if ($this->type == 'single') return time() - 1;
return strtotime("+1 $this->type");
  }
  
  protected function setid($id) {
    $this->data['id'] = $id;
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . $id;
  }
  
  protected function setfilename($filename) {
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . basename($filename, '.php');
    $this->Load();
  }
  
  public function delete() {
    $this->owner->delete($this->id);
  }
  
  public function execute() {
    global $options;
$this->owner->log("task started:\n{$this->class}->{$this->func}");
    
    $func = $this->func;
    if ($this->class == '' ) {
      if (!function_exists($func)) return $this->Delete();
      try {
        $func($this->arg);
      } catch (Exception $e) {
        $options->handexception($e);
      }
    } else {
      if (!class_exists($this->class)) return $this->Delete();
      try {
        $obj = getinstance($this->class);
        $obj->$func($this->arg);
      } catch (Exception $e) {
        $options->handexception($e);
      }
    }
    
    if ($this->type == 'single') {
      $this->Delete();
    } else {
      $this->time  = $this->GetExpired();
      $this->save();
    }
  }
  
} //class

class tcron extends tevents {
  public $disableadd;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'cron';
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . 'index';
    $this->data['url'] = '';
    $this->data['autoid'] = 0;
    $this->data['path'] = '';
    $this->cache = false;
    $this->disableadd = false;
  }
  
  public function getpath() {
    global $paths;
    if (($this->data['path'] != '') && is_dir($this->data['path'])) {
      return  $this->data['path'];
    }
    return $this->getdir();
  }
  
  protected function getdir() {
    global $paths;
    return  $paths['data'] . 'cron' . ;DIRECTORY_SEPARATOR);
  }
  
  public function request($arg) {
    if ($fh = @fopen($this->path .'cron.lok', 'w')) {
      flock($fh, LOCK_EX);
      ignore_user_abort(true);
      set_time_limit(60*20);
      $this->sendexceptions();
      $this->log("started loop");
      $this->execute();
      flock($fh, LOCK_UN);
      fclose($fh);
      $this->log("finished loop");
      $this->pop();
      return 'Ok';
    }
    return 'locked';
  }
  
  public function execute() {
    @ob_end_flush ();
    echo "<pre>\n";
    $time = time();
    $task = new TCronTask($this);
    $processed = array();
    while ($filelist = $this->GetFileList($processed)) {
      //var_dump($filelist);
      foreach ($filelist as $filename) {
        $processed[] = $filename;
        $task->filename = $filename;
        //var_dump($task->data);
        //echo $time - $task->time;
        //echo date("r\n", $task->time);
        if  ($time >= $task->time)  $task->Execute();
      }
    }
    return true;
  }
  
  private function GetFileList(&$processed) {
    $result = array();
    foreach (glob($this->dir . '*.php') as $filename) {
      if (!preg_match('/\d+\.php$/', $filename)) continue;
      if (in_array($filename, $processed)) continue;
      $result[] = $filename;
    }
    if (count($result) == 0) return false;
    return $result;
  }
  
  public function add($type, $class, $func, $arg = null) {
    if ($this->disableadd) return false;
    ++$this->data['autoid'] ;
    $this->Save();
    $task = new TCronTask($this);
    $task->Add($this->autoid, $type, $class, $func, $arg );
    if (($type == 'single') && !defined('cronpinged')) {
      define('cronpinged', true);
      register_shutdown_function('TCron::SelfPing');
    }
    return $this->autoid;
  }
  
  public function delete($id) {
    @unlink($this->dir . $id . '.php');
    @unlink($this->dir . $id . '.bak.php');
  }
  
  public function deleteclass($class) {
    $task = new TCronTask($this);
    $processed = array();
    if ($filelist = $this->GetFileList($processed)) {
      foreach ($filelist as $filename) {
        $task->filename = $filename;
        if ($task->class == $class) $task->Delete();
      }
    }
  }
  
  public static function SelfPing() {
global $options;
try {
    $self = getinstance(__class__);
    $cronfile =$self->dir .  'crontime.txt';
    @file_put_contents($cronfile, ' ');
    @chmod($cronfile, 0666);
    
    $self->ping();
    } catch (Exception $e) {
      $options->handexception($e);
    }

  }
  
  public function ping() {
    global $options, $domain;
    $this->AddToChain($domain, $options->subdir . $this->url);
    $this->PingHost($domain, $options->subdir . $this->url);
  }
  
  private function PingHost($host, $path) {
    //$this->log("pinged host $host$path");
    if (		$socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs( $socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
    }
  }
  
  private function pop() {
    global $domain;
    $host = $domain;
    $filename = $this->path .'cronchain.php';
    if(!tfiler::unserialize($filename, $list))  return;
    if (isset($list[$host]))  unset($list[$host]);
    $item = array_splice($list, 0, 1);
    tfiler::serialize($filename, $list);
    if ($item) {
      $this->PingHost(key($item), $item[key($item)]);
    }
  }
  
  private function AddToChain($host, $path) {
    $filename = $this->path .'cronchain.php';
    if(!tfiler::unserialize($filename, $list)) {
      $list = array();
    }
    if (!isset($list[$host])) {
      $list[$host] = $path;
      tfiler::serialize($filename, $list);
    }
  }
  
  public function sendexceptions() {
    global $paths, $options;
    //проверить, если файл логов создан более часа назад, то его отослать на почту
    $filename = $paths['data'] . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
    $time = @filectime ($filename);
    if (($time === false) || ($time + 3600 > time())) return;
    $s = file_get_contents($filename);
    @unlink($filename);
    tmailer::SendAttachmentToAdmin("[error] $options->name", "See attachment", 'errors.txt', $s);
  }
  
  public function log($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    if (defined('debug')) tfiler::log($s, 'cron.log');
  }
  
}//class

?>