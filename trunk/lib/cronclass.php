<?php

class TCronTask extends TDataClass {
  public $owner;
  
  public function __construct(&$owner) {
    $this->owner = &$owner;
    parent::__construct();
  }
  
  protected function CreateData() {
    $this->Data= array(
    'id' => 0,
    'type' => 'single',
    'time' => 0,
    'class' => '',
    'func' => '',
    'arg' => ''
    );
  }
  
  public function Add($id, $type, $class, $func, $arg) {
    if (!in_array($type, array('single', 'hour', 'day', 'week'))) return $this->Error("unknown cron task $type");
    $this->id = $id;
    $this->type= $type;
    $this->class = $class;
    $this->func = $func;
    $this->arg = $arg;
    $this->time = $this->GetExpired();
    $this->Save();
  }
  
  public function GetExpired() {
    switch ($this->type) {
      case 'single': return time() - 1;
      case 'hour': return time() + 60*60;
      case 'day': return time() + 3600 * 24;
      case 'week': return time() + 3600 * 24 *7;
      default: return time();
    }
  }
  
  protected function Setid($id) {
    $this->Data['id'] = $id;
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . $id;
  }
  
  protected function Setfilename($filename) {
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . basename($filename, '.php');
    $this->Load();
  }
  
  public function Delete() {
    global $paths;
    @unlink($paths['data'] . $this->GetBaseName() .'.php');
    @unlink($paths['data'] . $this->GetBaseName() .'.bak.php');
    $this->owner->AppendLog("task deleted ". $paths['data'] . $this->GetBaseName() .'.php');
  }
  
  public function Execute() {
$this->owner->AppendLog("task started:\n{$this->class}->{$this->func}");
    
    $func = $this->func;
    if ($this->class == '' ) {
      if (!function_exists($func)) return $this->Delete();
      try {
        $func($this->arg);
      } catch (Exception $e) {
        $this->owner->AppendLog('Caught exception: '.  $e->getMessage() . "\ntrace error\n" . $e->getTraceAsString());
      }
    } else {
      if (!class_exists($this->class)) return $this->Delete();
      try {
        $Obj = &GetInstance($this->class);
        $Obj->$func($this->arg);
      } catch (Exception $e) {
        $this->owner->AppendLog('Caught exception: '.  $e->getMessage() . "\ntrace error\n" . $e->getTraceAsString());
      }
    }
    
    if ($this->type == 'single') {
      $this->Delete();
    } else {
      $this->time  = $this->GetExpired();
      $this->Save();
    }
  }
  
} //class

class TCron extends TEventClass {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'cron' . DIRECTORY_SEPARATOR . 'index';
    $this->Data['url'] = '';
    $this->Data['lastid'] = 0;
    $this->CacheEnabled = false;
  }
  
  public function Request($arg) {
    global $paths;
    $LokFileName = $paths['home']. 'data' . DIRECTORY_SEPARATOR.'cron.lok';
    if ($fh = @fopen($LokFileName, 'w')) {
      flock($fh, LOCK_EX);
      ignore_user_abort(true);
      set_time_limit(60*20);
      $this->AppendLog("started loop");
      $this->ExecuteTasks();
      flock($fh, LOCK_UN);
      fclose($fh);
      $this->AppendLog("finished loop");
      $this->PopChain();
      return 'Ok';
    }
    return 'locked';
  }
  
  private function GetDir() {
    global $paths;
    return $paths['data'] . 'cron' . DIRECTORY_SEPARATOR;
  }
  
  public function ExecuteTasks() {
    ob_end_flush ();
    echo "<pre>\n";
    $time = time();
    $task = new TCronTask($this);
    $processed = array();
    while ($filelist = $this->GetFileList($processed)) {
      foreach ($filelist as $filename) {
        $processed[] = $filename;
        $task->filename = $filename;
        //var_dump($task->Data);
        if  ($time >= $task->time)  $task->Execute();
      }
    }
    return true;
  }
  
  private function GetFileList(&$processed) {
    $result = array();
    $filelist = TFiler::GetFileList($this->GetDir());
    foreach ($filelist as $filename) {
      if (!preg_match('/\d+\.php$/', $filename)) continue;
      if (in_array($filename, $processed)) continue;
      $result[] = $filename;
    }
    if (count($result) == 0) return false;
    return $result;
  }
  
  public function Add($type, $class, $func, $arg = null) {
    ++$this->Data['lastid'] ;
    $this->Save();
    $task = new TCronTask($this);
    $task->Add($this->lastid, $type, $class, $func, $arg );
    if (($type == 'single') && !defined('cronpinged')) {
      define('cronpinged', true);
      register_shutdown_function('TCron::SelfPing');
    }
    return $this->lastid;
  }
  
  public function Remove($id) {
    global $paths;
    @unlink($paths['data'] . 'cron' . DIRECTORY_SEPARATOR . $id . '.php');
    @unlink($paths['data'] . 'cron' . DIRECTORY_SEPARATOR . $id . '.bak.php');
  }
  
  public function RemoveClass($class) {
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
    global $paths;
    $cronfile =$paths['data'] . 'cron' . DIRECTORY_SEPARATOR.  'crontime.txt';
    @file_put_contents($cronfile, ' ');
    @chmod($cronfile, 0666);
    $self = &GetInstance(__class__);
    $self->Ping();
  }
  
  public function Ping() {
    global $Options, $domain;
    $this->AddToChain($domain, $Options->subdir . $this->url);
    $this->PingHost($domain, $Options->subdir . $this->url);
  }
  
  private function PingHost($host, $path) {
    //$this->AppendLog("pinged host $host$path");
    if (		$socket = @fsockopen( $host, 80, $errno, $errstr, 0.10)) {
      fputs( $socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
    }
  }
  
  private function PopChain() {
    global $paths, $domain;
    $host = $domain;
    $filename = $paths['home']. 'data' . DIRECTORY_SEPARATOR.'cronchain.php';
    if(!TFiler::UnserializeFromFile($filename, $list))  return;
    if (isset($list[$host]))  unset($list[$host]);
    $item = array_splice($list, 0, 1);
    TFiler::SerializeToFile($filename, $list);
    if ($item) {
      $this->PingHost(key($item), $item[key($item)]);
    }
  }
  
  private function AddToChain($host, $path) {
    global $paths;
    $filename = $paths['home']. 'data' . DIRECTORY_SEPARATOR.'cronchain.php';
    if(!TFiler::UnserializeFromFile($filename, $list)) {
      $list = array();
    }
    if (!isset($list[$host])) {
      $list[$host] = $path;
      TFiler::SerializeToFile($filename, $list);
    }
  }
  
  public function AppendLog($s) {
    echo date('r') . "\n$s\n\n";
    flush();
    return;
    global $paths;
    $filename = $paths['home']. 'data' . DIRECTORY_SEPARATOR.'cronlog.txt';
    if ($fp = fopen($filename,"a+")) {
      fwrite($fp, date('r') . "\n$s\n\n");
      fclose($fp);
      @chmod($filename, 0666);
    }
  }
  
}//class

?>