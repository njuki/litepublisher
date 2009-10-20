<?php

function __autoload($ClassName) {
  global $classes;
  if ($path =$classes->getpath($ClassName)) {
    $filename = $path . $classes->items[$ClassName][0];
    if (@file_exists($filename)) {
      require_once($filename);
    }
  }
}

class TClasses extends TItems {
  public $classes;
  public $instances;
  
  public static function instance() {
    return getinstance(__class__);
  }

public function getinstance($class) {
  if (!class_exists($class)) {
    $this->error("Class $class not found");
  }
  if (!isset($this->instances[$class])) {
    $this->instances[$class] = new $class();
  }
  return $this->instances[$class];
}
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->AddDataMap('classes', array());
    $this->instances = array();
  }

public function __get($name) {
if (isset($this->classes[$name])) return getinstance($this->classes[$name]);
return parent::__get($name);
}
  
  public function add($class, $filename, $path = '') {
    if (!isset($this->items[$class]) ||
    ($this->items[$class][0] != $filename) || ($this->items[$class][1] != $path)) {
      $this->items[$class] = array($filename, $path);
      $this->save();
      $instance = getinstance($class);
      if (method_exists($instance, 'install')) $instance->install();
    }
    $this->added($class);
  }
  
  public function delete($clsss) {
    if (isset($this->items[$class])) {
      if (class_exists($class)) {
        $instance = getinstance($class);
        if (method_exists($instance, 'uninstall')) $instance->uninstall();
      }
      unset($this->items[$class]);
      $this->save();
      $this->deleted($ClassName);
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
  
  public function getpath($class) {
    global  $paths;
    if (!isset($this->items[$class])) return false;
    if (empty($this->items[$class][1])) return $paths['lib'];
    
    $result = rtrim($this->items[$class][1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (@is_dir($result))  return $result;
    
    //may be is subdir?
    if (@is_dir($paths['plugins']. $result)) return $paths['plugins']. $result;
    if (@is_dir($paths['themes']. $result)) return $paths['themes']. $result;
    if  (@is_dir($paths['home'] . $result)) return  $paths['home'] . $result;
    
    return false;
  }
  
}//class

function getinstance($class) {
  global $classes;
return $classes->getinstance($class);
}

function PHPComment(&$s) {
  $s = str_replace('*/', '**//*/', $s);
  return "<?php /* $s */ ?>";
}

function PHPUncomment(&$s) {
  $s = substr($s, 9, strlen($s) - 9 - 6);
  return str_replace('**//*/', '*/', $s);
}

function SafeSaveFile($BaseName, &$Content) {
  $TmpFileName = $BaseName.'.tmp.php';
  if(!file_put_contents($TmpFileName, $Content))  return false;
  @chmod($TmpFileName , 0666);
  $FileName = $BaseName.'.php';
  if (@file_exists($FileName)) {
    $BakFileName = $BaseName . '.bak.php';
    @unlink($BakFileName);
    rename($FileName, $BakFileName);
  }
  return rename($TmpFileName, $FileName);
}

?>