<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdata {
  public static $savedisabled;
  public $basename;
  public $cache;
  public $coclasses;
  public $coinstances;
  public $data;
  public $lockcount;
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
        if (isset($coinstance->$name)) return $coinstance->$name;
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
      if (isset($coinstance->$name)) {
        $coinstance->$name = $value;
        return true;
      }
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array($this, strtolower($name)), $params);
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name)) return call_user_func_array(array($coinstance, $name), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->data) || method_exists($this, "get$name") | method_exists($this, "Get$name");
  }
  
  public function error($Msg) {
    throw new Exception($Msg);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->externalchain('Install');
  }
  
  public function uninstall() {
    $this->externalchain('Uninstall');
  }
  
  public function validate($repair = false) {
    $this->externalchain('Validate', $repair);
  }
  
  protected function externalchain($func, $arg = null) {
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      $this->externalfunc($class, $func, $arg);
    }
  }
  
  protected function externalfunc($class, $func, $arg) {
    if ($filename = litepublisher::$classes->getclassfilename($class)) {
      $externalname = basename($filename, '.php') . '.install.php';
      $dir = dirname($filename) . DIRECTORY_SEPARATOR;
      $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
      if (!file_exists($file)) {
        $file =$dir .  $externalname;
        if (!file_exists($file)) return;
      }
      
      include_once($file);
      $fnc = $class . $func;
      if (function_exists($fnc)) $fnc($this, $arg);
    }
  }
  
  public function load() {
    //if ($this->dbversion == 'full') return $this->LoadFromDB();
return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    if ($this->dbversion) {
      $this->SaveToDB();
    } else {
tfilestorage::save($this);
    }
  }
  
  public function savetostring() {
    return serialize($this->data);
  }
  
  public function loadfromstring($s) {
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
    return false; // dbversion == 'full';
  }
  
  public function getdb($table = '') {
    $table =$table != '' ? $table : $this->table;
    if ($table != '') litepublisher::$db->table = $table;
    return litepublisher::$db;
  }
  
  protected function SaveToDB() {
    $this->db->add($this->getbasename(), $this->savetostring());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->loadfromstring($r['data']);
    }
  }
  
  protected function getthistable() {
    return litepublisher::$db->prefix . $this->table;
  }
  
}//class

class tfilestorage {
public static $disabled;

public static function save(tdata $obj) {
if (self::$disabled) return false;
      return self::savetofile(litepublisher::$paths->data .$obj->getbasename(), self::comment_php($obj->savetostring()));
}

public static function load(tdata $obj) {
    $filename = litepublisher::$paths->data . $obj->getbasename() .'.php';
    if (file_exists($filename)) {
      return $obj->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
return false;
}

  public static function savetofile($base, $content) {
    $tmp = $base .'.tmp.php';
    if(false === file_put_contents($tmp, $content)) {
      litepublisher::$options->trace("Error write to file $tmp");
      return false;
    }
    chmod($tmp, 0666);
    $filename = $base .'.php';
    if (file_exists($filename)) {
      $back = $base . '.bak.php';
      if (file_exists($back)) unlink($back);
      rename($filename, $back);
    }
    if (!rename($tmp, $filename)) {
      litepublisher::$options->trace("Error rename file $tmp to $filename");
      return false;
    }
    return true;
  }
  
  public static function comment_php($s) {
    return sprintf('<?php /* %s */ ?>', str_replace('*/', '**//*/', $s));
  }
  
  public static function uncomment_php($s) {
    return str_replace('**//*/', '*/', substr($s, 9, strlen($s) - 9 - 6));
  }
  
}//class

class tstorage extends tfilestorage {
private static $data;
private static $modified;

public static function save(tdata $obj) {
self::$modified = true;
$base = $obj->getbasename();
      if (!isset(self::$data[$base])) self::$data[$base] = &$obj->data;
return true;
}

public static function load(tdata $obj) {
$base = $obj->getbasename();
      if (isset(self::$data[$base])) {
        $obj->data = &self::$data[$base];
        $obj->afterload();
return true;
      } else {
        self::$data[$base] = &$obj->data;
return false;
      }
}

public static function savemodified() {
    if (self::$modified) {
if (self::$disabled) return false;
self::savetofile(litepublisher::$paths->data .'storage', self::comment_php(serialize(self::$data)));
    self::$modified = false;
return true;
}
return false;
  }

public static function loaddata() {
self::$data = array();
    $filename = litepublisher::$paths->data . 'storage.php';
    if (file_exists($filename)) {
$s = self::uncomment_php(file_get_contents($filename));
if (!empty($s)) self::$data = unserialize($s);
return true;
}
return false;
}

}//class

class tarray2prop {
  public $array;
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
public function __isset($name) { return array_key_exists($name, $this->array); }
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

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function array_delete(array &$a, $i) {
  array_splice($a, $i, 1);
}

function array_delete_value(array &$a, $value) {
  $i = array_search($value, $a);
  if ($i !== false)         array_splice($a, $i, 1);
}

function array_insert(array &$a, $item, $index) {
  array_splice($a, $index, 0, array($item));
}

function array_move(array &$a, $oldindex, $newindex) {
  //delete and insert
  if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
  $item = $a[$oldindex];
  array_splice($a, $oldindex, 1);
  array_splice($a, $newindex, 0, array($item));
}

function dumpstr($s) {
  echo "<pre>\n" . htmlspecialchars($s) . "</pre>\n";
}

?>