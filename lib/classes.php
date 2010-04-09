<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

if (!function_exists( 'spl_autoload_register' ) ) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $instances;
  
  public static function instance() {
    if (!isset(litepublisher::$classes)) {
      $class = __class__;
      litepublisher::$classes = new $class();
      litepublisher::$classes->instances[$class] = litepublisher::$classes;
    }
    return litepublisher::$classes;
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->dbversion = false;
    $this->addevents('onnewitem');
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->instances = array();
    if (function_exists('spl_autoload_register')) spl_autoload_register(array(&$this, '_autoload'));
  }
  
  public function getinstance($class) {
    if (!class_exists($class)) {
      $this->error("Class $class not found");
    }
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = $this->newinstance($class);
    }
    return $this->instances[$class];
  }
  
  public function newinstance($class) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    return new $class();
  }
  
  public function newitem($name, $class, $id) {
    //echo"$name:$class:$id new<br>\n";
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    $this->callevent('onnewitem', array($name, &$class, $id));
    return new $class();
  }
  
  public function __get($name) {
    if (isset($this->classes[$name])) return $this->getinstance($this->classes[$name]);
    $class = 't' . $name;
    if (isset($this->items[$class])) return $this->getinstance($class);
    return parent::__get($name);
  }
  
  public function add($class, $filename, $path = '') {
    if (!isset($this->items[$class]) ||
    ($this->items[$class][0] != $filename) || ($this->items[$class][1] != $path)) {
      $this->items[$class] = array($filename, $path);
      //$this->save();
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'install')) $instance->install();
    }
    $this->save();
    $this->added($class);
  }
  
  public function delete($class) {
    if (isset($this->items[$class])) {
      if (class_exists($class)) {
        $instance = $this->getinstance($class);
        if (method_exists($instance, 'uninstall')) $instance->uninstall();
      }
      unset($this->items[$class]);
      $this->save();
      $this->deleted($class);
    }
  }
  
  public function reinstall($class) {
    if (isset($this->items[$class])) {
      $this->lock();
      $item = $this->items[$class];
      $this->delete($class);
      $this->add($class, $item[0], $item[1]);
      $this->unlock();
    }
  }
  
  public function _autoload($class) {
    if ($filename = $this->getclassfilename($class)) {
      if (file_exists($filename)) require_once($filename);
    }
    return false;
  }
  
  public function getclassfilename($class) {
    if ($path =$this->getpath($class))  return $path . $this->items[$class][0];
    if (isset($this->interfaces[$class])) return litepublisher::$paths->lib . $this->interfaces[$class];
    return false;
  }
  
  public function getpath($class) {
    if (!isset($this->items[$class])) return false;
    if (empty($this->items[$class][1])) return litepublisher::$paths->lib;
    $result = trim($this->items[$class][1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $filename = $result . $this->items[$class][0];
    if (file_exists($filename))  return $result;
    //may be is subdir?
    if (file_exists(litepublisher::$paths->plugins . $filename)) return litepublisher::$paths->plugins . $result;
    if (file_exists(litepublisher::$paths->themes . $filename)) return litepublisher::$paths->themes . $result;
    if  (file_exists(litepublisher::$paths->home . $filename)) return  litepublisher::$paths->home . $result;
    return false;
  }
  
}//class

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}

?>