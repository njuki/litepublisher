<?php

class TFiler {
  
  public static function DeleteFiles($path, $subdirs , $rmdir = false) {
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        if (@is_dir($path . $filename)) {
          if ($subdirs) self::DeleteFiles($path . $filename . DIRECTORY_SEPARATOR, $subdirs, $rmdir);
        } else {
          unlink($path . $filename);
        }
      }
      @closedir($h);
    }
    if ($rmdir) rmdir($path);
  }
  
  public static function DeleteFilesExt($path, $ext) {
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
  
  public static function GetFileList($path) {
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
  
  public static function GetDirList($dir) {
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
  
  public static function UnserializeFromFile($FileName, &$v) {
    if ($s = @file_get_contents($FileName)) {
      $s =PHPUncomment($s);
      if (!empty($s)) {
        $v = unserialize($s);
        return true;
      }
    }
    return false;
  }
  
  public static function SerializeToFile($FileName, &$v) {
    $s = serialize($v);
    $s =  PHPComment($s);
    file_put_contents($FileName, $s);
    @chmod($FileName, 0666);
  }
  
  public static function log($s) {
    global $paths;
    $filename = $paths['data'] . 'log.txt';
    if ($fp = fopen($filename,"a+")) {
      fwrite($fp, date('r') . "\n$s\n\n");
      fclose($fp);
      @chmod($filename, 0666);
    }
  }
  
}//class
?>