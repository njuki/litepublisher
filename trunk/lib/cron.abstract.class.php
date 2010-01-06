<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tabstractcron extends tevents {
  public $disableadd;
  
  protected function create() {
    parent::create();
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . 'index';
    $this->data['password'] = '';
    $this->data['path'] = '';
    $this->cache = false;
    $this->disableadd = false;
  }
  
  protected function getdir() {
    global $paths;
    return $paths['data'] . 'cron' . DIRECTORY_SEPARATOR;
  }
  
  protected function getpath() {
    global $paths;
    if (($this->data['path'] != '') && is_dir($this->data['path'])) {
      return  $this->data['path'];
    }
    return  $this->getdir();
  }
  
  protected function geturl() {
    global $options;
  return "/croncron.htm{$options->q}cronpass=$this->password";
  }
  
  public function request($arg) {
    if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) return 404;
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
  
  protected function execute() {
    $this->error('call abstract method execute');
  }
  
  protected function doadd($type, $class, $func, $arg)  {
    $this->error('call abstract method doadd');
  }
  
  public function add($type, $class, $func, $arg = null) {
    if (!preg_match('/single|hour|day|week/', $type)) $this->error("Unknown cron type $type");
    if ($this->disableadd) return false;
    $id = $this->doadd($type, $class, $func, $arg);
    
    if (($type == 'single') && !defined('cronpinged')) {
      define('cronpinged', true);
      register_shutdown_function('TCron::SelfPing');
    }
    return $id;
  }
  
  public static function SelfPing() {
    global $options;
    try {
      $self = getinstance(__class__);
      $cronfile = $self->dir .  'crontime.txt';
      @file_put_contents($cronfile, ' ');
      @chmod($cronfile, 0666);
      
      $self->ping();
    } catch (Exception $e) {
      $options->handexception($e);
    }
  }
  
  public function ping() {
    global $options, $urlmap;
    $this->AddToChain($urlmap->host, $options->subdir . $this->url);
    $this->PingHost($urlmap->host, $options->subdir . $this->url);
  }
  
  private function PingHost($host, $path) {
    //$this->log("pinged host $host$path");
    if (		$socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs( $socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
    }
  }
  
  private function pop() {
    $filename = $this->path .'cronchain.php';
    if(!tfiler::unserialize($filename, $list))  return;
    $urlmap = turlmap::instance();
    if (isset($list[$urlmap->host]))  unset($list[$urlmap->host]);
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
    TMailer::SendAttachmentToAdmin("[error] $options->name", "See attachment", 'errors.txt', $s);
    sleep(2);
  }
  
  public function log($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    if (defined('debug')) tfiler::log($s, 'cron.log');
  }
  
}//class

?>