<?php

class TItem extends TDataClass {
  public static $AllItems;
  //public $id;
  protected $Aliases;
  
  public static function &Instance($ClassName, $id = 0) {
    if (!isset(self::$AllItems)) self::$AllItems = array();
    if (!isset(self::$AllItems[$ClassName]))  self::$AllItems[$ClassName] = array();
    if (isset(self::$AllItems[$ClassName][$id]))     return self::$AllItems[$ClassName][$id];
    $self = &new $ClassName ();
    $self->id = $id;
    if ($id != 0) {
      self::$AllItems[$ClassName][$id] = &$self;
      $self->Load();
    }
    return $self;
  }
  
  public function free() {
    unset(self::$AllItems[get_class($this)][$this->id]);
  }
  
  public function __construct() {
    parent::__construct();
    $this->Data['id'] = 0;
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
  
  public function Setid($id) {
    if ($id != $this->id) {
      $class = get_class($this);
      self::$AllItems[$class][$id] = &$this;
      if (isset(   self::$AllItems[$class][$this->id])) unset(self::$AllItems[$class][$this->id]);
      $this->Data['id'] = $id;
    }
  }
  
  public function Request($id) {
    $this->id = $id;
    $this->load();
  }
  
  public static function DeleteItemDir($dir) {
    global $paths;
    if (!@file_exists($dir)) return false;
    TFiler::DeleteFiles($dir, true, true);
    @rmdir($dir);
  }
  
  protected function SaveToDB() {
    $this->db->UpdateProps($this, $this->tablefields);
  }
  
  protected function LoadFromDB() {
    if ($res = $this->db->select("id = $this->id limit 1")) {
      $res->fetch(PDO::FETCH_INTO, $this);
    }
  }
  
}

?>