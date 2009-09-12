<?php

function __autoload($ClassName) {
  global $classes;
  if ($path =$classes->GetPath($ClassName)) {
    $filename = $path . $classes->items[$ClassName][0];
    if (@file_exists($filename)) {
      require_once($filename);
    }
  }
}

class TClasses extends TItems {
  public $classes;
  public $instances;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'classes';
    $this->AddDataMap('classes', array());
    $this->instances = array();
  }
  
  public function Add($ClassName, $FileName, $Path = '') {
    if (!isset($this->items[$ClassName]) ||
    ($this->items[$ClassName][0] != $FileName) || ($this->items[$ClassName][1] != $Path)) {
      $this->items[$ClassName] = array($FileName, $Path);
      $this->Save();
      $instance = &GetInstance($ClassName);
      if (method_exists($instance, 'Install')) $instance->Install();
    }
    $this->Added($ClassName);
  }
  
  public function Delete($ClassName) {
    if (isset($this->items[$ClassName])) {
      if (@class_exists($ClassName)) {
        $instance = &GetInstance($ClassName);
        if (method_exists($instance, 'Uninstall')) $instance->Uninstall();
      }
      unset($this->items[$ClassName]);
      $this->Save();
      $this->Deleted($ClassName);
    }
  }
  
  public function Reinstall($class) {
    if (isset($this->items[$class])) {
      $this->Lock();
      $item = $this->items[$class];
      $this->Delete($class);
      $this->Add($class, $item[0], $item[1]);
      $this->Unlock();
    }
  }
  
  public function GetPath($class) {
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

function &GetInstance($ClassName) {
  global $classes;
  if (!class_exists($ClassName)) {
    $classes->Error("Class $ClassName not found");
  }
  if (!isset($classes->instances[$ClassName])) {
    $classes->instances[$ClassName] = &new $ClassName ();
  }
  return $classes->instances[$ClassName];
}

function &GetNamedInstance($name, $defclass) {
  global $classes;
  $class = !empty($classes->classes[$name]) ? $classes->classes[$name] : $defclass;
  return GetInstance($class);
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