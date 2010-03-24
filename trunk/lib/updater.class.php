<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tupdater extends tevents {
  public $version;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->addevents('onupdated');
$this->basename = 'updater';
    $this->version =  self::getversion();
  }
  
  public static function GetVersion() {
    return trim(file_get_contents(litepublisher::$paths->libinclude . 'version.txt'));
  }
  
  public function update() {
    $log = false;
    if ($log) tfiler::log("begin update", 'update');
    tfiler::deletemask(litepublisher::$paths->languages . '*.php');
    $this->version =  self::getversion();
    if ($log) tfiler::log("update started from litepublisher::$options->version to $this->version", 'update');
    $dir = litepublisher::$paths->lib . 'update' . DIRECTORY_SEPARATOR;
    $v = litepublisher::$options->version + 0.01;
    while ( $v<= $this->version) {
      if ($log) tfiler::log("$v selected to update", 'update');
      $filename = $dir . "update.$v.php";
      if (file_exists($filename)) {
        require_once($filename);
        if ($log) tfiler::log("$filename is required file", 'update');
        $func = 'update' . str_replace('.', '', $v);
        if (function_exists($func)) {
          $func();
          if ($log) tfiler::log("$func is called", 'update');
        }
      }
      $v = $v + 0.01;
    }
    
    litepublisher::$options->version = $v;
    
    $urlmap = turlmap::instance();
    $urlmap->clearcache();
    if ($log) tfiler::log("update finished", 'update');
  }
  
  public function autoupdate() {
    $lang = tlocal::instance('service');
    $this->createbackup();
    $result = $this->download($this->latest);
    if ($result === true) {
      $result = $lang->successdownload;
      $this->update($result);
      $result .= $lang->successupdated;
    }
    return $result;
  }
  
  public function islatest() {
    if ($latest = $this->getlatest()) {
      return litepublisher::$options->version >= $latest;
    }
    return 'error';
  }
  
  public function getlatest() {
    if (($s = http::get('http://litepublisher.com/service/version.txt'))  ||
    ($s = http::get('http://litepublisher.googlecode.com/files/version.txt') )) {
      return $s;
    }
    return false;
  }
  
  public function createbackup(){
    $backuper = tbackuper::instance();
    $s = $backuper->getpartial(true, true, true);
    $date = date('Y-m-d');
    $filename = litepublisher::$paths->backup . litepublisher::$domain . "-$date.'.tar.gz";
    file_put_contents($filename, $s);
    chmod($filename, 0666);
  }
  
  public function download($version) {
    $lang = tlocal::instance('service');
    //test write
    if (!@file_put_contents(litepublisher::$paths->lib . 'index.htm', ' ')) {
      return sprintf($lang->errorwrite, litepublisher::$paths->lib);
    }
    
    if (!($s = http::get("http://litepublisher.googlecode.com/files/litepublisher.$version.tar.gz")) &&
    !($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz") )) {
      return $lang->erordownload;
    }
    
    require_once(litepublisher::$paths->libinclude . 'tar.class.php');
    $tar = new tar();
    $tar->loadfromstring($s);
    foreach ($tar->files as $file) {
      if (      $filename = $this->fixfilename($file['name'])) {
        if (!tfiler::forcedir(dirname($filename))) return $this->error("error create folder " . dirname($filename));
        if (false === @file_put_contents($filename, $file['file'])) {
          return sprintf($lang->errorwritefile, $filename);
        }
        chmod($filename, 0666);
      }
    }
$this->onupdated($tar);
    return true;
  }
  
  private function fixfilename($filename) {
    foreach (array('lib', 'plugins') as $dir) {
      if (strbegin($filename, $dir . '/')) {
        $filename = substr($filename, strlen($dir) + 1);
        return litepublisher::$paths->$dir . str_replace('/', DIRECTORY_SEPARATOR, $filename);
      }
    }
    return false;
  }
  
}//class
?>