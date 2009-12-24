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
  
  public function getpartial($plugins, $theme, $lib) {
    global $paths;
    $tar = new tar();
    if (dbversion) $tar->addstring($this->getdump(), 'dump.sql', 0644);
    $this->readdir($tar, $paths['data'], '', 'data/');
    if ($lib)  $this->readdir($tar, $paths['lib'], '', 'lib/');
    if ($theme)  {
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
  
  public function setdump(&$dump) {
    $dbmanager = tdbmanager ::instance();
    return $dbmanager->import($dump);
  }
  
  public function uploaddump($s) {
    if($s[0] == chr(31) && $s[1] == chr(139) && $s[2] == chr(8)) {
      $s = gzinflate(substr($s,10,-4));
    }
    return $this->setdump($s);
  }
  
  public function upload(&$content) {
    global $paths;
    $tmp = false;
    $dataprefix = 'data/';
    $themesprefix =  'themes/';
    $pluginsprefix = 'plugins/';
    
    $tar = new tar();
    $tar->loadfromstring($content);
    foreach ($tar->files as $file) {
      $filename = $file['name'];
      if (dbversion && $filename == 'dump.sql') $this->setdump($file['file']);
      if (strbegin($filename, $dataprefix)) {
        $filename = substr($filename, strlen($dataprefix));
        if (!$tmp) $tmp = $this->createtemp();
        $path = $tmp;
      } elseif (strbegin($filename, $themesprefix)) {
        $filename = substr($filename, strlen($themesprefix));
        $path = $paths['themes'];
      } elseif (strbegin($filename, $pluginsprefix)) {
        $filename = substr($filename, strlen($pluginsprefix));
        $path = $paths['plugins'];
      } else {
        //echo $dir, " is unknown dir<br>";
      }
      
      $filename = $path . str_replace('/', DIRECTORY_SEPARATOR  , $filename);
      if (!tfiler::forcedir(dirname($filename))) return $this->error("error create folder " . dirname($filename));
      if (false === @file_put_contents($filename, $file['file'])) return $this->error("Error saving file $filename");
      @chmod($filename, $file['mode']);
    }
    
    if ($tmp) {
      $old = $up . basename($paths['data']) . '.old-tmp.tmp' . DIRECTORY_SEPARATOR;
      @rename($paths['data'], $old);
      @rename($tmp, $paths['data']);
      tfiler::delete($old, true, true);
    }
    
    return true;
  }
  
  private function createtemp() {
    global $paths;
    $result = dirname($paths['data']) .DIRECTORY_SEPARATOR . basename($paths['data']) . '.tmp.tmp' . DIRECTORY_SEPARATOR;
    @mkdir($result, 0777);
    @chmod($result, 0777);
    return $result;
  }
  
  public function getfull() {
    global $paths;
    $tar = new tar();
    $this->readdir($tar, $paths['data'], '', 'data/');
    
    foreach (tfiler::getdir($paths['plugins']) as $name ) {
      $this->readdir($tar, $paths['plugins'], $name, "plugins/");
    }
    
    foreach (tfiler::getdir($paths['themes']) as $name ) {
      $this->readdir($tar, $paths['themes'] , $name, "themes/");
    }
    
    $this->readdir($tar, $paths['lib'], '', 'lib/');
    $this->readdir($tar, $paths['files'], '', 'files/');
    
    return $tar->savetostring(true);
  }
  
}//class
?>