<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tremoteadmin extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'remoteadmin';
  }
  
  public function getclasses() {
    global $classes;
    return $classes->items;
  }
  
  public function existstheme($name) {
    global $paths;
    return @is_dir($paths['themes'] . $name);
  }
  
  public function pluginexists($name) {
    global $paths;
    return @is_dir($paths['plugins']. $name);
  }
  
  public function getthemes() {
    global $paths;
    return tfiler::getdir($paths['themes']);
  }
  
  public function getplugins() {
    global $paths;
    return tfiler::getdir($paths['plugins']);
  }
  
  public function setplugins($names) {
    $plugins = tplugins::instance();
    $plugins->setplugins($names);
  }
  
  public function deleteplugins($names) {
    $plugins = tplugins::instance();
    $plugins->deleteplugins($names);
  }
  
  public function settheme($name) {
    $template = ttemplate::instance();
    $template->theme = $name;
  }
  
  protected function  ReadDirToZip(&$zip, $path, $subdir, $prefix = '') {
    $subdirslashed = str_replace(DIRECTORY_SEPARATOR   , '/', $subdir) . '/';
    $subdirslashed  = ltrim($subdirslashed , '/');
    $hasindex = false;
    if ($fp = @opendir($path . $subdir)) {
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..')) continue;
        $filename = $path . $subdir .DIRECTORY_SEPARATOR . $file;
        if (@is_dir($filename)) {
          $this->ReadDirToZip($zip, $path, $subdir . DIRECTORY_SEPARATOR   . $file, $prefix);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $file)) continue;
          $zip->addFile(file_get_contents($filename), "$prefix$subdirslashed$file");
          if (!$hasindex) $hasindex = ($file == 'index.php') || ($file == 'index.htm');
        }
      }
    }
    if (!$hasindex) $zip->addFile('', $prefix . $subdirslashed. 'index.htm');
  }
  
  protected function GetDirAsZip($dir) {
    global $paths;
    require_once($paths['libinclude'] . 'zip.lib.php');
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $dir, '');
    return $zip->file();
  }
  
  protected function RequireZip() {
    global $paths;
    require_once($paths['libinclude'] . 'zip.lib.php');
  }
  
  public function DownloadPlugin($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['plugins'] . $name, '', "plugins/$name/");
    return $zip->file();
  }
  
  public function DownloadTheme($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['themes'] . $name, '', "themes/$name/");
    return $zip->file();
  }
  
  public function GetPartialBackup($plugins, $theme, $lib) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    if (dbversion) $zip->addFile($this->getdump(), 'dump.sql');
    $this->ReadDirToZip($zip, $paths['data'], '', 'data/');
    if ($lib) {
      $this->ReadDirToZip($zip, $paths['lib'], '', 'lib/');
    }
    if ($theme) {
      $Template = &TTemplate::instance();
      $themename = $Template->themename;
      $this->ReadDirToZip($zip, $paths['themes'] . $themename, '', "themes/$themename/");
    }
    
    if ($plugins) {
      $plugins = &TPlugins::instance();
      foreach ($plugins->items as $name => $item) {
        if (@is_dir($paths['plugins'] . $name)) {
          $this->ReadDirToZip($zip, $paths['plugins'] . $name, '', "plugins/$name/");
        }
      }
    }
    
    return $zip->file();
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
    
    require_once($paths['libinclude'] . 'strunzip.lib.php');
    $unzip = new StrSimpleUnzip ();
    $unzip->ReadData($content);
    foreach ($unzip->Entries as  $entry) {
      if ($entry->Error != 0) continue;
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
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['data'], '', 'data/');
    
    $items = tfiler::getdir($paths['plugins']);
    foreach ($items as $name ) {
      $this->ReadDirToZip($zip, $paths['plugins'], $name, "plugins/");
    }
    
    $items = tfiler::getdir($paths['themes']);
    foreach ($items as $name ) {
      $this->ReadDirToZip($zip, $paths['themes'] , $name, "themes/");
    }
    
    $this->ReadDirToZip($zip, $paths['lib'], '', 'lib/');
    $this->ReadDirToZip($zip, $paths['files'], '', 'files/');
    
    return $zip->file();
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