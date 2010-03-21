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
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->dbversion = false;
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->instances = array();
if (function_exists('spl_autoload_register')) spl_autoload_register(array(&$this, '_autoload'));
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
    if ($path =$this->getpath($class)) {
      $filename = $path . $this->items[$class][0];
    } elseif (isset($this->interfaces[$class])) {
      $filename = litepublisher::$paths->lib . $this->interfaces[$class];
    } else {
      //$this->error("$class class not found");
      return false;
    }
    if (file_exists($filename)) require_once($filename);
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

function PHPComment($s) {
  $s = str_replace('*/', '**//*/', $s);
  return "<?php /* $s */ ?>";
}

function PHPUncomment($s) {
  $s = substr($s, 9, strlen($s) - 9 - 6);
  return str_replace('**//*/', '*/', $s);
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function SafeSaveFile($BaseName, $Content) {
  $TmpFileName = $BaseName.'.tmp.php';
  if(!file_put_contents($TmpFileName, $Content)) {
    litepublisher::$options->trace("Error write to file $TmpFileName");
    return false;
  }
  chmod($TmpFileName , 0666);
  $FileName = $BaseName.'.php';
  if (file_exists($FileName)) {
    $BakFileName = $BaseName . '.bak.php';
    if (file_exists($BakFileName)) unlink($BakFileName);
    rename($FileName, $BakFileName);
  }
  if (!rename($TmpFileName, $FileName)) {
    litepublisher::$options->trace("Error rename file $TmpFileName to $FileName");
    return false;
  }
  return true;
}

?>