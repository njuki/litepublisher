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

class tcron extends tabstractcron {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function execute() {
if (ob_get_level()) ob_end_flush ();
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
  
  protected function doadd($type, $class, $func, $arg) {
    ++$this->data['autoid'] ;
    $this->Save();
    $task = new TCronTask($this);
    $task->Add($this->autoid, $type, $class, $func, $arg );
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
  
}//class
?>