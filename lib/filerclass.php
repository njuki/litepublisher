<?php

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
    if ($rmdir) rmdir($path);
  }
  
  public static function extdelete($path, $ext) {
    if ($fp = @opendir($path )) {
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..') || ($file == '.svn')) continue;
        $filename = $path . $file;
        if (@is_dir($filename))  continue;
        if (preg_match("/(\\.$ext\$)/",  $file)) {
          unlink($filename);
        }
      }
    }
  }

//clear cache
  public static function deleteregexp($path, $regexp) {
    if ($fp = @opendir($path )) {
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..') || ($file == '.svn')) continue;
        $filename = $path . $file;
        if (@is_dir($filename)) {
self::deleteregexp($filename. DIRECTORY_SEPARATOR, $regexp);
} else {
        if (preg_match($regexp, $file)) {
          unlink($filename);
}
        }
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
  
  public static function log($s, $filename = '') {
    global $paths;
    $dir = $paths['data'] . 'logs' . DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) {
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
    }
    if ($filename == '') $filename = 'log.txt';
    $filename = $dir . $filename;
    if ($fp = fopen($filename,"a+")) {
      fwrite($fp, date('r') . "\n$s\n\n");
      fclose($fp);
      @chmod($filename, 0666);
    }
  }
  
}//class
?>