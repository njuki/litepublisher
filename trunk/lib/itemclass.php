<?php

class TItem extends TDataClass {
  public static $AllItems;
  public $id;
  protected $Aliases;
  
  public static function &Instance($ClassName, $id = 0) {
    if (!isset(self::$AllItems)) self::$AllItems = array();
    if (!isset(self::$AllItems[$ClassName]))  self::$AllItems[$ClassName] = array();
    if (!isset(self::$AllItems[$ClassName][$id])) {
      self::$AllItems[$ClassName][$id] = &new $ClassName ();
      $self = &self::$AllItems[$ClassName][$id];
      $self->id = $id;
      if ($id > 0) $self->Load($id);
    }
    return self::$AllItems[$ClassName][$id];
  }
  
  public function __construct() {
    parent::__construct();
    $this->id = 0;
  }
  
  public function __get($name) {
    if (isset($this->Aliases[$name])) {
      return $this->Data[$this->Aliases[$name]];
    }
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    
    if (isset($this->Aliases[$name])) {
      return $this->__set($this->Aliases[$name],  $value);
    }
    
    return  $this->Error("Field $name not exists in class " . get_class($this));
  }
  
  private function Setid($id) {
    if ($id != $this->id) {
      $class = get_class($this);
      if (isset(   self::$AllItems[$class][$this->id])) {
        self::$AllItems[$class][$this->id] = null;
        unset(self::$AllItems[$class][$this->id]);
      }
      $this->id = $id;
      self::$AllItems[$class][$id] = &$this;
    }
  }
  
  public function Request($id) {
    $this->id = $id;
    $this->Load();
  }
  
  public static function DeleteItemDir($dir) {
    global $paths;
    if (!@file_exists($dir)) return false;
    TFiler::DeleteFiles($dir, true, true);
    @rmdir($dir);
  }
  
}

?>