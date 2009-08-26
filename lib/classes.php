<?php

function __autoload($ClassName) {
  if ($path =TClasses::GetPath($ClassName)) {
    $filename = $path . TClasses::$items[$ClassName][0];
    if (@file_exists($filename)) {
      require_once($filename);
    }
  }
}

class TClasses {
  public static $items;
  public static $standart;
  public static $instances;
  private static $LockCount;
  
  public static function Register($ClassName, $FileName, $Path = '') {
    if (!isset(self::$items[$ClassName]) ||
    (self::$items[$ClassName][0] != $FileName) || (self::$items[$ClassName][1] != $Path)) {
      self::$items[$ClassName] = array($FileName, $Path);
      self::Save();
      $instance = &GetInstance($ClassName);
      if (method_exists($instance, 'Install')) $instance->Install();
    }
  }
  
  public static function Unregister($ClassName) {
    if (isset(self::$items[$ClassName])) {
      if (@class_exists($ClassName)) {
        $instance = &GetInstance($ClassName);
        if (method_exists($instance, 'Uninstall')) $instance->Uninstall();
      }
      unset(self::$items[$ClassName]);
      self::Save();
    }
  }
  
  public static function Reinstall($class) {
    if (isset(self::$items[$class])) {
      self::Lock();
      $item = self::$items[$class];
      self::Unregister($class);
      self::Register($class, $item[0], $item[1]);
      self::Unlock();
    }
  }
  
  public static function Save() {
    global $paths;
    if (self::$LockCount > 0) return;
    $s = serialize(self::$items);
    $s = PHPComment($s);
    SafeSaveFile($paths['data'].'classes', $s);
  }
  
  public static  function Load() {
    global $paths;
    if (!isset(self::$items)) {
      self::$items = array();
    }
    if ($s = @file_get_contents($paths['data'].'classes.php')) {
      $s = PHPUncomment($s);
      if (!empty($s)) self::$items = unserialize($s);
    }
  }
  
  public static function Lock() {
    self::$LockCount++;
  }
  
  public static function Unlock() {
    if (--self::$LockCount <= 0) self::Save();
  }
  
  public static function GetPath($class) {
    global  $paths;
    if (!isset(TClasses::$items[$class])) return false;
    if (empty(TClasses::$items[$class][1])) return $paths['lib'];
    
    $result = rtrim(TClasses::$items[$class][1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (@is_dir($result))  return $result;
    
    //may be is subdir?
    if (@is_dir($paths['plugins']. $result)) return $paths['plugins']. $result;
    if (@is_dir($paths['themes']. $result)) return $paths['themes']. $result;
    if  (@is_dir($paths['home'] . $result)) return  $paths['home'] . $result;
    
    return false;
  }
  
}//class

function &GetInstance($ClassName) {
  if (!@class_exists($ClassName)) return null;
  if (!isset(TClasses::$instances[$ClassName])) {
    TClasses::$instances[$ClassName] = &new $ClassName ();
  }
  return TClasses::$instances[$ClassName];
}

function &GetStandartInstance($name) {
return GetInstance(TClasses::$standart[$name]);
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