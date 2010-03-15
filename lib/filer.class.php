<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfiler {
  
  public static function delete($path, $subdirs , $rmdir = false) {
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (@is_dir($file)) {
          if ($subdirs) self::delete($file . DIRECTORY_SEPARATOR, $subdirs, $rmdir);
        } else {
          unlink($file);
        }
      }
      @closedir($h);
    }
    if ($rmdir) @rmdir($path);
  }
  
  public static function deletemask($mask) {
    if ($list = glob($mask)) {
      foreach ($list as $filename) unlink($filename);
    }
  }
  
  public static function deletedirmask($path, $mask) {
    foreach (glob($path. $mask) as $filename) {
      if (@is_dir($filename)) {
        self::deletedirmask($filename. DIRECTORY_SEPARATOR, $mask);
      } else {
        unlink($filename);
      }
    }
  }
  
  public static function getfiles($path) {
    $result = array();
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        if (!@is_dir($path . $filename)) $result[] = $filename;
      }
      @closedir($h);
    }
    return $result;
  }
  
  public static function getdir($dir) {
    $result = array();
    if ($fp = @opendir($dir)) {
      while (FALSE !== ($file = readdir($fp))) {
        if (@is_dir($dir.$file)  && ($file != '.') && ($file != '..') && ($file != '.svn')){
          $result[] = $file;
        }
      }
    }
    return $result;
  }
  
  public static function forcedir($dir) {
    if (@is_dir($dir)) return true;
    $up = dirname(trim($dir, DIRECTORY_SEPARATOR));
    if (($up != '') || ($up != '.'))  self::forcedir($up);
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    return @is_dir($dir);
  }
  
  public static function unserialize($FileName, &$v) {
    if ($s = @file_get_contents($FileName)) {
      $s =PHPUncomment($s);
      if (!empty($s)) {
        $v = unserialize($s);
        return true;
      }
    }
    return false;
  }
  
  public static function serialize($FileName, &$v) {
    $s = serialize($v);
    $s =  PHPComment($s);
    file_put_contents($FileName, $s);
    @chmod($FileName, 0666);
  }
  
  public static function ini2js(array $a, $filename) {
    $sections = array();
    foreach ($a as $name => $section) {
      if ($name == 'default' || $name == 'delete') $name = 'a' . $name;
      $list = array();
      foreach ($section as $key => $value) {
        if ($key == 'default' || $key == 'delete') $key = 'a' . $key;
        $value = str_replace("\r\n", '\n', $value);
        $value = str_replace("\n", '\n', $value);
        $list[] = "$key:\"$value\"";
      }
    $sections[] = sprintf("$name:{\n%s\n}", implode(",\n", $list));
    }
  $s = sprintf("var lang= {\n%s\n};\n", implode(",\n", $sections));
    file_put_contents($filename, $s);
    @chmod($filename, 0666);
  }
  
  public static function log($s, $filename = '') {
    if ($filename == '') $filename = 'log.txt';
    self::append(date('r') . "\n$s\n\n", litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR . $filename);
  }
  
  public static function append($s, $filename) {
    $dir = dirname($filename);
    if (!is_dir($dir)) {
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
    }
    
    if ($fp = fopen($filename,"a+")) {
      fwrite($fp, $s);
      fclose($fp);
      @chmod($filename, 0666);
    }
  }
  
  public static function get_filetime_offset() {
    $filename = litepublisher::$paths->data . md5uniq() . '.tmp';
    $t = time();
    touch($filename, $t, $t);
    clearstatcache  ();
    $t2 = filemtime($filename);
    unlink($filename);
    return $t2  - $t;
  }
  
}//class
?>