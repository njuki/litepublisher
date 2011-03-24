<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tupdater extends tevents {
  public $version;
  public $result;
  public $log;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('onupdated');
    $this->basename = 'updater';
    $this->version =  self::getversion();
    $this->log = false;
  }
  
  public static function getversion() {
    return trim(file_get_contents(litepublisher::$paths->libinclude . 'version.txt'));
  }
  
  public function run($version) {
    $filename =     litepublisher::$paths->lib . 'update' . DIRECTORY_SEPARATOR . "update.$version.php";

    if (file_exists($filename)) {
      require_once($filename);
      if ($this->log) tfiler::log("$filename is required file", 'update');
      $func = 'update' . str_replace('.', '', $version);
      if (function_exists($func)) {
        $func();
        if ($this->log) tfiler::log("$func is called", 'update');
        litepublisher::$options->savemodified();
      }
    }
  }
  
  public function update() {
    $log =$this->log;false;
    if ($log) tfiler::log("begin update", 'update');
    tlocal::clearcache();
    $this->version =  self::getversion();
    if ($log) tfiler::log("update started from litepublisher::$options->version to $this->version", 'update');
    $v = litepublisher::$options->version + 0.01;
    while ( $v<= $this->version) {
      $ver = (string) $v;
      if (strlen($ver) == 3) $ver .= '0';
      if ($log) tfiler::log("$v selected to update", 'update');
      $this->run($v);
      $v = $v + 0.01;
    }
    
    litepublisher::$options->version = $this->version;
    ttheme::clearcache();
    tlocal::clearcache();
    if ($log) tfiler::log("update finished", 'update');
  }
  
  public function autoupdate() {
    //protect timeout
    if (ob_get_level()) @ob_end_clean ();
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    echo "\n";
    flush();
    $lang = tlocal::instance('service');
    $backuper = tbackuper::instance();
    $backuper->createbackup();
    if ($this->download($this->latest)) {
      $this->result = $lang->successdownload;
      $this->update();
      $this->result .= $lang->successupdated;
      return true;
    }
    return false;
  }
  
  public function auto2($ver) {
    $lang = tlocal::instance('service');
    $latest = $this->latest;
    if($latest == litepublisher::$options->version) return 'Already updated';
    if (($ver == 0) || ($ver > $latest)) $ver = $latest;
    if ($this->download($ver)) {
      $this->result = $lang->successdownload;
      $this->update();
      $result .= $lang->successupdated;
      return true;
    }
    return false;
  }
  
  public function islatest() {
    if ($latest = $this->getlatest()) {
      return $latest - litepublisher::$options->version ;
    }
    return false;
  }
  
  public function getlatest() {
    if (($s = http::get('http://litepublisher.com/service/version.txt'))  ||
    ($s = http::get('http://litepublisher.googlecode.com/svn/trunk/lib/include/version.txt') )) {
      return $s;
    }
    return false;
  }
  
  public function download($version) {
    $lang = tlocal::instance('service');
    $backuper = tbackuper::instance();
    if (!$backuper->test()) {
      $this->result = $lang->errorwrite;
      return  false;
    }
    
    if (!(
    ($s = http::get("http://litepublisher.googlecode.com/files/litepublisher.$version.tar.gz")) ||
    ($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz"))
    )) {
      $this->result = $lang->erordownload;
      return  false;
    }
    
    if (!$backuper->upload($s, 'tar')) {
      $this->result = $backuper->result;
      return false;
    }
    
    $this->onupdated();
    return true;
  }
  
}//class
?>