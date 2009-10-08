<?php

class TDataClass {
  private $LockCount;
  public static $GlobalLock;
  public $Data;
  public $basename;
  public $CacheEnabled;
  //database
  public $table;
  
  public function __construct() {
    $this->LockCount = 0;
    $this->CacheEnabled = true;
    $this->Data= array();
    $this->basename = 'data';
    $this->CreateData();
  }
  
  protected function CreateData() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "Get$name") ||
method_exists($this, $get = "get$name")) {
      return $this->$get();
    } elseif (array_key_exists($name, $this->Data)) {
      return $this->Data[$name];
    } else {
      return    $this->Error("The requested property $name not found in class ". get_class($this));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "Set$name")) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->Data)) {
      $this->Data[$name] = $value;
      return true;
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(&$this, strtolower($name)), $params);
    }
    $this->Error("The requested method $name not found in class " . get_class($this));
  }
  
  public function PropExists($name) {
    return isset($this->$name) || array_key_exists($name, $this->Data) || method_exists($this, "Get$name");
  }
  
  public function Error($Msg) {
    throw new Exception($Msg);
  }
  
  public function GetBaseName() {
    return $this->basename;
  }
  
  public function Install() {
    $this->CallSatellite('Install');
  }
  
  public function Uninstall() {
    $this->CallSatellite('Uninstall');
  }
  
  public function Validate($repair = false) {
    $this->CallSatellite('Validate', $repair);
  }
  
  protected function CallSatellite($func, $arg = null) {
    global $classes, $paths;
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      if ($path = $classes->GetPath($class)) {
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
    if ($this->dbversion == 'full') return $this->LoadFromDB();
      $FileName = $paths['data'] . $this->GetBaseName() .'.php';
      if (@file_exists($FileName)) {
        return $this->LoadFromString(PHPUncomment(file_get_contents($FileName)));
      }
  }
  
  public function save() {
    global $paths;
    if (self::$GlobalLock || ($this->LockCount > 0)) return;
    if ($this->dbversion == 'full') {
      $this->SaveToDB();
    } else {
      SafeSaveFile($paths['data'].$this->GetBaseName(), $this->SaveToString());
    }
  }
  
  public function SaveToFile($FileName) {
    if ($fh = fopen($FileName, 'w+')) {
      $this->SaveToStream($fh);
      fclose($fh);
    } else {
      $this->Error("Cannt open $FileName to write");
    }
  }
  
  public function SaveToStream($handle) {
    $s = $this->SaveToString();
    fwrite($handle, $s);
  }
  
  public function LoadFromFile($FileName) {
    if ($fh = fopen($FileName, 'r')) {
      $this->LoadFromStream($fh, filesize($FileName));
      fclose($fh);
    } else {
      $this->Error("Cant open $FileName to read");
    }
  }
  
  public function  LoadFromStream($handle, $length) {
    $s = fread($handle,  $length);
    $this->LoadFromString($s);
  }
  
  public function SaveToString() {
    return PHPComment(serialize($this->Data));
  }
  
  public function LoadFromString($s) {
    try {
      if (!empty($s)) $this->Data = unserialize($s) + $this->Data;
      $this->AfterLoad();
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
    }
  }
  
  public function AfterLoad() {
  }
  
  public function lock() {
    $this->LockCount++;
  }
  
  public function Unlock() {
    if (--$this->LockCount <= 0) $this->Save();
  }
  
  public function Getlocked() {
    return $this->LockCount  > 0;
  }
  
  public function Getdbversion() {
    return false;
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
    $db->add($this->GetBaseName(), $this->SaveToString());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->GetBaseName() . "'")) {
      return $this->LoadFromString($r['data']);
    }
  }
  
}//class
?>