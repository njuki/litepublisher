<?php

class titem extends tdata {
  public static $instances;
  //public $id;
  protected $aliases;
  
  public static function instance($class, $id = 0) {
global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = $classes->newinstance($class);
    $self->id = $id;
    if ($id != 0) {
      if (!$self->load()) {
$self->free();
return false;
}
      self::$instances[$class][$id] = $self;
    }
    return $self;
  }
  
  public function free() {
    unset(self::$instances[get_class($this)][$this->id]);
  }
  
  public function __construct() {
    parent::__construct();
    $this->data['id'] = 0;
  }
  
  public function __get($name) {
    if (isset($this->aliases[$name])) {
      return $this->data[$this->aliases[$name]];
    }
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    
    if (isset($this->aliases[$name])) {
      return $this->__set($this->aliases[$name],  $value);
    }
    
    return  $this->Error("Field $name not exists in class " . get_class($this));
  }
  
  public function setid($id) {
    if ($id != $this->id) {
      $class = get_class($this);
      self::$instances[$class][$id] = $this;
      if (isset(   self::$instances[$class][$this->id])) unset(self::$instances[$class][$this->id]);
      $this->data['id'] = $id;
    }
  }
  
  public function request($id) {
    $this->id = $id;
    if (!$this->load()) return 404;
  }
  
  public static function DeleteItemDir($dir) {
    global $paths;
    if (!@file_exists($dir)) return false;
    tfiler::delete($dir, true, true);
    @rmdir($dir);
  }
  
}

?>