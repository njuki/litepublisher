<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tbackuper extends tevents {

  public static function instance() {
    return getinstance(__class__);
  }
  
 protected function create() {
global $paths;
    parent::create();
require_once($paths['libinclude'] . 'tar.class.php');
  }

  private function  readdir(tar $tar, $path, $subdir, $prefix = '') {
    $subdirslashed = str_replace(DIRECTORY_SEPARATOR   , '/', $subdir) . '/';
    $subdirslashed  = ltrim($subdirslashed , '/');
    $hasindex = false;
    if ($fp = @opendir($path . $subdir)) {
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..')) continue;
        $filename = $path . $subdir .DIRECTORY_SEPARATOR . $file;
        if (@is_dir($filename)) {
          $this->readdir($tar, $path, $subdir . DIRECTORY_SEPARATOR   . $file, $prefix);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $file)) continue;
          $tar->add($filename, "$prefix$subdirslashed$file", 0666);
          if (!$hasindex) $hasindex = ($file == 'index.php') || ($file == 'index.htm');
        }
      }
    }
    if (!$hasindex) $tar->add('', $prefix . $subdirslashed. 'index.htm', 0666);
  }
  
  private function dirtotar($dir, $gz) {
$tar = new tar();
    $this->readdir($tar, $dir, '');
    return $tar->savetostring($gz);
  }
  
  public function DownloadPlugin($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->readdir($zip, $paths['plugins'] . $name, '', "plugins/$name/");
    return $zip->file();
  }
  
  public function DownloadTheme($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->readdir($zip, $paths['themes'] . $name, '', "themes/$name/");
    return $zip->file();
  }
  
  public function GetPartialBackup($plugins, $theme, $lib) {
    global $paths;
$tar = new tar();
if (dbversion) $tar->add($this->dump, 'dump.sql');
    $this->readdir($tar, $paths['data'], '', 'data/');
    if ($lib)  $this->readdir($tar, $paths['lib'], '', 'lib/');
    if ($theme)  
      $template = ttemplate::instance();
      $themename = $template->theme;
      $this->readdir($tar, $paths['themes'] . $themename, '', "themes/$themename/");
    }
    
    if ($plugins) {
      $plugins = tplugins::instance();
      foreach ($plugins->items as $name => $item) {
        if (@is_dir($paths['plugins'] . $name)) {
          $this->readdir($tar, $paths['plugins'] . $name, '', "plugins/$name/");
        }
      }
    }
    
    return $tar->savetostring(true);
  }

public function getdump() {
$dbmanager = tdbmanager ::instance();
return $dbmanager->export();
}

  public function Upload(&$content) {
    global $paths;
    $dataprefix = 'data';
    $themesprefix =  'themes/';
    $pluginsprefix = 'plugins/';
    
$tar = new tar();
    $tar->loadfromstring($content);
    foreach ($tar->files as $file) {
      $dir = $entry->Path;
      if ($dataprefix == substr($dir, 0, strlen($dataprefix))) {
        $dir = substr($dir, strlen($dataprefix));
        if (!isset($tmp)) {
          $up = dirname($paths['data']) .DIRECTORY_SEPARATOR;
          $tmp = $up . basename($paths['data']) . '-tmp.tmp' . DIRECTORY_SEPARATOR;
          @mkdir($tmp, 0777);
          @chmod($tmp, 0777);
        }
        $path = $tmp;
      } elseif ($themesprefix == substr($dir, 0, strlen($themesprefix))) {
        $dir = substr($dir, strlen($themesprefix));
        $path = $paths['themes'];
      } elseif ($pluginsprefix == substr($dir, 0, strlen($pluginsprefix))) {
        $dir = substr($dir, strlen($pluginsprefix));
        $path = $paths['plugins'];
      } else {
        //echo $dir, " is unknown dir<br>";
      }
      
      $dir = str_replace('/', DIRECTORY_SEPARATOR  , $dir);
      if (!$this->ForceDirectories($path, $dir)) return $this->Error("cantcreate folder $path$dir");
      $filename = $path . $dir . DIRECTORY_SEPARATOR    . $entry->Name;
      if (false === @file_put_contents($filename, $entry->Data)) {
        return $this->Error("Error saving file $filename");
      }
      @chmod($filename, 0666);
    }
    
    if (isset($tmp)) {
      $old = $up . basename($paths['data']) . '-old-tmp.tmp' . DIRECTORY_SEPARATOR;
      @rename($paths['data'], $old);
      @rename($tmp, $paths['data']);
      tfiler::delete($old, true, true);
    }
    
    return true;
  }
  
  public function GetFullBackup() {
    global $paths;
    $this->RequireZip();
    $tar = new zipfile();
    $this->readdir($tar, $paths['data'], '', 'data/');
    
    $items = tfiler::getdir($paths['plugins']);
    foreach ($items as $name ) {
      $this->readdir($tar, $paths['plugins'], $name, "plugins/");
    }
    
    $items = tfiler::getdir($paths['themes']);
    foreach ($items as $name ) {
      $this->readdir($tar, $paths['themes'] , $name, "themes/");
    }
    
    $this->readdir($tar, $paths['lib'], '', 'lib/');
    $this->readdir($tar, $paths['files'], '', 'files/');
    
    return $tar->file();
  }
  
  protected function ForceDirectories($path, $dir) {
    if (!@is_dir($path . $dir)) {
      $up = dirname($dir);
      if (($up != '') || ($up != '.'))   $this->ForceDirectories($path, $up);
      if (!@mkdir($path . $dir, 0777)) return $this->Error("cant create $dir folder");
      @chmod($path . $dir, 0777);
    }
    return true;
  }
  
}//class
?>