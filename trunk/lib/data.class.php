<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tdata {
  private $lockcount;
  public static $GlobalLock;
  public $data;
  public $basename;
  public $CacheEnabled;
  //database
  public $table;
  
  public function __construct() {
    $this->lockcount = 0;
    $this->CacheEnabled = true;
    $this->data= array();
    $this->basename = 'data';
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name") ||
    method_exists($this, $get = "Get$name")) {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
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
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(&$this, strtolower($name)), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function PropExists($name) {
    return array_key_exists($name, $this->data) || method_exists($this, "get$name") | method_exists($this, "Get$name") || isset($this->$name);
  }
  
  public function supported($interface) {
    return is_a($this, $interface);
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
    global $classes, $paths;
$func{0} = strtoupper($func{0});
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      if ($path = $classes->getpath($class)) {
        $filename = basename($classes->items[$class][0], '.php') . '.install.php';
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
    global $paths;
    if (dbversion == 'full') return $this->LoadFromDB();
    $filename = $paths['data'] . $this->getbasename() .'.php';
    if (@file_exists($filename)) {
      return $this->LoadFromString(PHPUncomment(file_get_contents($filename)));
    }
  }
  
  public function save() {
    global $paths;
    if (self::$GlobalLock || ($this->lockcount > 0)) return;
    if (dbversion == 'full') {
      $this->SaveToDB();
    } else {
      SafeSaveFile($paths['data'].$this->getbasename(), PHPComment($this->SaveToString()));
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
  
  public function Getlocked() {
    return $this->lockcount  > 0;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function Getdb($table = 'data') {
    global $db;
    $table =$table != '' ? $table : $this->table;
    if ($table != '') $db->table = $table;
    return $db;
  }
  
  protected function SaveToDB() {
    $db->add($this->getbasename(), $this->SaveToString());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->LoadFromString($r['data']);
    }
  }
  
protected function getthistable() {
global $db;
return $db->prefix . $this->table;
}

protected function geturltable() {
global $db;
return $db->prefix .'urlmap';
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
}//class

function sqldate($date = 0) {
if ($date == 0) $date = time();
return date('Y-m-d H:i:s', $date);
}

function dbqote($s) {
global $db;
return $db->quote($s);
}

function md5uniq() {
return md5(mt_rand() . secret. microtime());
}

?>