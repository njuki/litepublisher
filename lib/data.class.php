<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdata {
  public $lockcount;
  public static $GlobalLock;
  public $data;
  public $coinstances;
  public $coclasses;
  public $basename;
  public $cache;
  //database
  public $table;
  
  public function __construct() {
    $this->lockcount = 0;
    $this->cache= true;
    $this->data= array();
    $this->coinstances = array();
    $this->coclasses = array();
    $this->basename = substr(get_class($this), 1);
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name"))  {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      foreach ($this->coinstances as $coinstance) {
        if ($coinstance->propexists($name)) return $coinstance->$name;
      }
      return    $this->error("The requested property $name not found in class ". get_class($this));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->data)) {
      $this->data[$name] = $value;
      return true;
    }
    
    foreach ($this->coinstances as $coinstance) {
      if ($coinstance->propexists($name)) {
        $coinstance->$name = $value;
        return true;
      }
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(&$this, strtolower($name)), $params);
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name)) return call_user_func_array(array($coinstance, $name), $params);
    }
    
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function propexists($name) {
    return array_key_exists($name, $this->data) || method_exists($this, "get$name") | method_exists($this, "Get$name") || isset($this->$name);
  }
  
  public function error($Msg) {
    throw new Exception($Msg);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->CallSatellite('install');
  }
  
  public function uninstall() {
    $this->CallSatellite('uninstall');
  }
  
  public function validate($repair = false) {
    $this->CallSatellite('validate', $repair);
  }
  
  protected function CallSatellite($func, $arg = null) {
$func{0} = strtoupper($func{0});
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      if ($path = litepublisher::$classes->getpath($class)) {
        $filename = basename(litepublisher::$classes->items[$class][0], '.php') . '.install.php';
        $file =$path . 'install' . DIRECTORY_SEPARATOR . $filename;
        if (!@file_exists($file)) {
          $file =$path .  $filename;
          if (!@file_exists($file)) continue;
        }
        
        include_once($file);
        
        $fnc = $class . $func;
        if (function_exists($fnc)) $fnc($this, $arg);
      }
    }
  }
  
  public function load() {
    if (dbversion == 'full') return $this->LoadFromDB();
    $filename = litepublisher::$paths->data . $this->getbasename() .'.php';
    if (@file_exists($filename)) {
      return $this->LoadFromString(PHPUncomment(file_get_contents($filename)));
    }
  }
  
  public function save() {
    if (self::$GlobalLock || ($this->lockcount > 0)) return;
    if ($this->dbversion) {
      $this->SaveToDB();
    } else {
      SafeSaveFile(litepublisher::$paths->data .$this->getbasename(), PHPComment($this->SaveToString()));
    }
  }
  
  public function SaveToString() {
    return serialize($this->data);
  }
  
  public function LoadFromString($s) {
    try {
      if (!empty($s)) $this->data = unserialize($s) + $this->data;
      $this->afterload();
      return true;
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
      return false;
    }
  }
  
  public function afterload() {
  }
  
  public function lock() {
    $this->lockcount++;
  }
  
  public function unlock() {
    if (--$this->lockcount <= 0) $this->save();
  }
  
  public function getlocked() {
    return $this->lockcount  > 0;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function getdbversion() {
    return dbversion == 'full';
  }
  
  public function getdb($table = '') {
    $table =$table != '' ? $table : $this->table;
    if ($table != '') litepublisher::$db->table = $table;
    return litepublisher::$db;
  }
  
  protected function SaveToDB() {
    $this->db->add($this->getbasename(), $this->SaveToString());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->LoadFromString($r['data']);
    }
  }
  
  protected function getthistable() {
    return litepublisher::$db->prefix . $this->table;
  }
  
  protected function geturltable() {
    return litepublisher::$db->prefix .'urlmap';
  }
  
  protected function getjoinurl() {
    return " left join $this->urltable on $this->urltable.id = $this->thistable.idurl ";
  }
}//class

class tarray2prop {
  public $array;
public function __construct(array &$array) { $this->array = &$array; }
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
  return litepublisher::$db->quote($s);
}

function md5uniq() {
  return md5(mt_rand() . litepublisher::$secret. microtime());
}

?>