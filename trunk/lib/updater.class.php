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
    global $paths;
    parent::create();
    $this->basename = 'updater';
    $s = file_get_contents($paths['libinclude']. 'version.txt');
    $this->version =  (int) trim($s);
  }
  
  public static function GetVersion() {
    global $paths;
    $s = file_get_contents($paths['libinclude']. 'version.txt');
    return substr($s, 0, 1) . '.' . substr($s, 1);
  }
  
  public function Update() {
    global $Options, $paths;
    $log = false;
    if ($log) TFiler::log("begin update", 'update');
    tfiler::deletemask($paths['languages'] . '*.php');
    $s = file_get_contents($paths['libinclude']. 'version.txt');
    $this->version =  (int) trim($s);
  $current = ((int) $Options->version{0}) * 100 + (int)substr($Options->version, 2);
    if ($log) TFiler::log("update started from $current to $this->version", 'update');
    for ($v = $current + 1; $v<= $this->version; $v++) {
      if ($log) TFiler::log("$v selected to update", 'update');
      $dir = $v >= 264 ? $paths['lib'] . 'update' . DIRECTORY_SEPARATOR : $paths['libinclude'];
      $filename = $dir . "update$v.php";
      if (@file_exists($filename)) {
        require_once($filename);
        if ($log) TFiler::log("$filename is required file", 'update');
        $func = "Update$v";
        if (function_exists($func)) {
          $func();
          if ($log) TFiler::log("$func is called", 'update');
        }
      }
    }
    
    $Options->version = substr((string)$this->version, 0, 1) . '.' . substr((string)$this->version, 1);
    
    $urlmap = turlmap::instance();
    $urlmap->clearcache();
    if ($log) TFiler::log("update finished", 'update');
  }
  
  public function AutoUpdate() {
    $lang = &TLocal::$data['service'];
    $this->CreateBackup();
    $result = $this->DownloadLatest();
    if ($result === true) {
      $result = $lang['successdownloadlatest'];
      $this->Update();
      $result .= $lang['successupdated'];
    }
    return $result;
  }
  
  public function IsLatest() {
    global $Options, $paths;
    $current = (int) str_replace('.', '', $Options->version);
    include_once($paths['libinclude'] . 'utils.php');
    if (($s = GetWebPage('http://blogolet.ru/service/version.txt'))  ||
    ($s = GetWebPage('http://litepublisher.googlecode.com/files/version.txt') )) {
      return $current >= (int)$s;
    }
    return 'error';
  }
  
  public function CreateBackup(){
    global $paths, $domain;
    $admin = &TRemoteAdmin::instance();
    $s = $admin->GetPartialBackup(true, true, true);
    $date = date('Y-m-d');
    $filename = $paths['backup'] . "$domain-$date.zip";
    @file_put_contents($filename, $s);
    @chmod($filename, 0666);
  }
  
  public function DownloadLatest() {
    global $paths;
    $lang = &TLocal::$data['service'];
    //test write
    if (!@file_put_contents($paths['lib'] . 'index.htm', ' ')) {
      return sprintf($lang['errorwrite'], $paths['lib']);
    }
    
    include_once($paths['libinclude'] . 'utils.php');
    if (!($s = GetWebPage('http://litepublisher.googlecode.com/files/litepublisher.zip')) &&
    !($s = GetWebPage('http://blogolet.ru/service/blogolet.zip') )) {
      return $lang['erordownloadlatest'];
    }
    
    require_once($paths['libinclude'] . 'strunzip.lib.php');
    $unzip = new StrSimpleUnzip ();
    $unzip->ReadData($s);
    foreach ($unzip->Entries as  $entry) {
      if ($entry->Error != 0) {
        echo $entry->Path, $entry->Name, " error: ", $entry->ErrorMsg, "<br>\n";
        if ($entry->Error== 4) echo sprintf("%u = crc32<br>\n", crc32($entry->Data));
        continue;
      }
      $dir = $entry->Path;
      $root = 'lib';
      if (strncmp($root, $dir, strlen($root)) == 0) {
        $dir = substr($dir, strlen($root));
      } else {
        $root = 'plugins';
        if (strncmp($root, $dir, strlen($root)) == 0) {
          $dir = substr($dir, strlen($root));
        } else {
          continue;
        }
      }
      $dir = trim($dir, '/');
      if (!empty($dir)) $dir .= '/';
      $dir = str_replace('/', DIRECTORY_SEPARATOR  , $dir);
      $dir = $paths[$root] . $dir;
      if (!@is_dir($dir)) {
        @mkdir($dir, 0777);
        @chmod($dir, 0777);
      }
      $filename = $dir . $entry->Name;
      if (false === @file_put_contents($filename, $entry->Data)) {
        return sprintf($lang['errorwritefile'], $filename);
      }
      @chmod($filename, 0666);
    }
    return true;
  }
  
}//class
?>