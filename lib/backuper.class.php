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
    parent::create();
    require_once(litepublisher::$paths->libinclude . 'tar.class.php');
  }
  
  public function  readdir(tar $tar, $path, $subdir, $prefix = '') {
$path  = rtrim($path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR  ;
    $subdir = trim($subdir, DIRECTORY_SEPARATOR  );
    if ($subdir != '') $subdir .= DIRECTORY_SEPARATOR  ;
    $subdirslashed = str_replace(DIRECTORY_SEPARATOR   , '/', $subdir) ;
    $hasindex = false;
    if ($fp = opendir($path . $subdir)) {
      $tar->adddir($prefix. $subdirslashed, 0777);
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..')) continue;
        $filename = $path . $subdir .$file;
        if (is_dir($filename)) {
          $this->readdir($tar, $path, $subdir . $file, $prefix);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $file)) continue;
          $tar->add($filename, "$prefix$subdirslashed$file", 0666);
          if (!$hasindex) $hasindex = ($file == 'index.php') || ($file == 'index.htm');
        }
      }
    }
    if (!$hasindex) $tar->addstring('', $prefix . $subdirslashed. 'index.htm', 0666);
  }
  
  private function dirtotar($dir, $gz) {
    $tar = new tar();
    $this->readdir($tar, $dir, '');
    return $tar->savetostring($gz);
  }
  
  public function DownloadPlugin($name) {
    $this->RequireZip();
    $zip = new zipfile();
    $this->readdir($zip, litepublisher::$paths->plugins . $name, '', "plugins/$name/");
    return $zip->file();
  }
  
  public function DownloadTheme($name) {
    $this->RequireZip();
    $zip = new zipfile();
    $this->readdir($zip, litepublisher::$paths->themes . $name, '', "themes/$name/");
    return $zip->file();
  }
  
  public function getpartial($plugins, $theme, $lib) {
    $tar = new tar();
    if (dbversion) $tar->addstring($this->getdump(), 'dump.sql', 0644);
    $this->readdir($tar, litepublisher::$paths->data, '', 'data/');
    if ($lib)  {
      $this->readdir($tar, litepublisher::$paths->lib, '', 'lib/');
      $this->readdir($tar, litepublisher::$paths->js, '', 'js/');
    }
    if ($theme)  {
      $template = ttemplate::instance();
      $themename = $template->theme;
      $this->readdir($tar, litepublisher::$paths->themes . $themename . DIRECTORY_SEPARATOR  , '', "themes/$themename/");
    }
    
    if ($plugins) {
      $plugins = tplugins::instance();
      foreach ($plugins->items as $name => $item) {
        if (@is_dir(litepublisher::$paths->plugins . $name)) {
          $this->readdir($tar, litepublisher::$paths->plugins . $name, '', "plugins/$name/");
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
    $tmp = false;
    $dataprefix = 'data/';
    $themesprefix =  'themes/';
    $pluginsprefix = 'plugins/';
    $jsprefix = 'js/';
    
    $tar = new tar();
    $tar->loadfromstring($content);
    foreach ($tar->files as $file) {
      $filename = $file['name'];
      if (dbversion && $filename == 'dump.sql') {
        $this->setdump($file['file']);
        continue;
      }
      if (strbegin($filename, $dataprefix)) {
        $filename = substr($filename, strlen($dataprefix));
        if (!$tmp) $tmp = $this->createtemp();
        $path = $tmp;
      } elseif (strbegin($filename, $themesprefix)) {
        $filename = substr($filename, strlen($themesprefix));
        $path = litepublisher::$paths->themes;
      } elseif (strbegin($filename, $pluginsprefix)) {
        $filename = substr($filename, strlen($pluginsprefix));
        $path = litepublisher::$paths->plugins;
      } elseif (strbegin($filename, $jsprefix)) {
        $filename = substr($filename, strlen($jsprefix));
        $path = litepublisher::$paths->js;
      } else {
        //echo $dir, " is unknown dir<br>";
      }
      
      $filename = $path . str_replace('/', DIRECTORY_SEPARATOR  , $filename);
      if (!tfiler::forcedir(dirname($filename))) return $this->error("error create folder " . dirname($filename));
      if (false === file_put_contents($filename, $file['file'])) return $this->error("Error saving file $filename");
      
      //chmod($filename, $file['mode']);
      chmod($filename, 0666);
    }
    
    if ($tmp) {
      $old = dirname(litepublisher::$paths->data) .DIRECTORY_SEPARATOR . basename(litepublisher::$paths->data) . '.old-tmp.tmp' . DIRECTORY_SEPARATOR;
      @rename(litepublisher::$paths->data, $old);
      @rename($tmp, litepublisher::$paths->data);
      tfiler::delete($old, true, true);
    }
    return true;
  }
  
  private function createtemp() {
    $result = dirname(litepublisher::$paths->data) .DIRECTORY_SEPARATOR . basename(litepublisher::$paths->data) . '.tmp.tmp' . DIRECTORY_SEPARATOR;
    if (!is_dir($result)) mkdir($result, 0777);
    chmod($result, 0777);
    return $result;
  }
  
  public function getfull() {
    $tar = new tar();
    if (dbversion) $tar->addstring($this->getdump(), 'dump.sql', 0644);
    $this->readdir($tar, litepublisher::$paths->data, '', 'data/');
    
    foreach (tfiler::getdir(litepublisher::$paths->plugins) as $name ) {
      $this->readdir($tar, litepublisher::$paths->plugins, $name, "plugins/");
    }
    
    foreach (tfiler::getdir(litepublisher::$paths->themes) as $name ) {
      $this->readdir($tar, litepublisher::$paths->themes , $name, "themes/");
    }
    
    $this->readdir($tar, litepublisher::$paths->lib, '', 'lib/');
    $this->readdir($tar, litepublisher::$paths->js, '', 'js/');
    $this->readdir($tar, litepublisher::$paths->files, '', 'files/');
    
    return $tar->savetostring(true);
  }
  
  public function createbackup(){
    $s = $this->getpartial(true, true, true);
    $filename = litepublisher::$paths->backup . litepublisher::$domain . date('-Y-m-d') . '.tar.gz';
    file_put_contents($filename, $s);
    chmod($filename, 0666);
    return $filename;
  }
  
}//class
?>