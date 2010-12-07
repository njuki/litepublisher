<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackuper extends tevents {
public  $filertype;
private $tar;
private $zip;
private $unzip;
public $archtype;
private $__filer;
private $existingfolders;
private $lastdir;
private $stdfolders;
private $hasdata;

    public static function instance() {
    return getinstance(__class__);
  }
  
public static function include_tar() {
    require_once(litepublisher::$paths->libinclude . 'tar.class.php');
}

public static function include_zip() {
    require_once(litepublisher::$paths->libinclude . 'zip.lib.php');
}

public static function include_unzip() {
    require_once(litepublisher::$paths->libinclude . 'strunzip.lib.php');
}
  
  protected function create() {
    parent::create();
$this->basename = 'backuper';
$this->data['ftproot'] = '';
$this->__filer = null;
$this->tar = null;
$this->zip = null;
$this->unzip = null;
$this->archtype = 'zip';
$this->lastdir = '';
$this->filertype = self::getprefered();
  }

public function __destruct() {
unset($this->__filer, $this->tar, $this->zip, $this->unzip);
parent::__destruct();
}

public function unknown_archive() {
$this->error('Unknown archive type ' . $this->archtype);
}

public static function getprefered() {
$owner = fileowner(dirname(__file__));
if (($owner !== false) && ($owner === getmyuid())) return 'file';

if (extension_loaded('ssh2') && function_exists('stream_get_contents') ) return 'ssh2';
if (extension_loaded('ftp')) return 'ftp';
if (extension_loaded('sockets') || function_exists('fsockopen')) return 'socket';
return false;
}

public function getfiler() {
if (isset($this->__filer)) return $this->__filer;
switch ($this->filertype) {
case 'ftp':
$result = new tftpfiler();
break;

case 'ssh2':
$result = new tssh2filer();
break;

case 'socket':
$result = new tftpsocketfiler();
break;

case 'file':
$result = new tlocalfiler();
break;

default:
$this->filertype = 'file';
$result = new tlocalfiler();
$result->chmod_file = 0666;
$result->chmod_dir = 0777;
break;
}

$this->__filer = $result;
return $result;
}

public function connect($host, $login, $password) {
if ($this->filer->connected) return true;
if ($this->filer->connect($host, $login, $password)) {
if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) $this->check_ftp_root();
return true;
}
return false;
}

public function createarch() {
if (!$this->filer->connected) $this->error('Filer not connected');
switch ($this->archtype) {
case 'tar':
self::include_tar();
   $this->tar = new tar();
break;

case 'zip':
self::include_zip();
$this->zip = new zipfile();
break;

case 'unzip':
self::include_unzip();
$this->unzip = new StrSimpleUnzip ();
break;

default:
$this->unknown_archive();
}
}

public function savearchive() {
switch ($this->archtype) {
case 'tar':
$result = $this->tar->savetostring(true);
echo count($this->tar->files);
unset($this->tar);
return $result;

case 'zip':
$result = $this->zip->file();
echo count($this->zip->datasec);
unset($this->zip);
return $result;

default:
$this->unknown_archive();
}
}

private function addfile($filename, $content, $perm) {
switch ($this->archtype) {
case 'tar':
return $this->tar->addstring($content, $filename, $perm);

case 'zip':
          return $this->zip->addFile($content, $filename);

default:
$this->unknown_archive();
}
}

private function adddir($dir, $perm) {
switch ($this->archtype) {
case 'tar':
return $this->tar->adddir($dir, $perm);

case 'zip':
return true;

default:
$this->unknownarchive();
}
}

  private function  readdir($path) {
    $path  = rtrim($path, '/');
$filer = $this->getfiler();
    if ($list = $filer->getdir($path )) {
$this->adddir($path, $filer->getchmod($path));
$path .= '/';
    $hasindex = false;
foreach ($list as $name => $item) {
$filename = $path . $name;
        if ($item['isdir']) {
          $this->readdir($filename);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $name)) continue;
$this->addfile($filename,$filer->getfile($filename), $item['mode']);
          if (!$hasindex) $hasindex = ($name == 'index.php') || ($name == 'index.htm');
        }
      }
    if (!$hasindex) $this->addfile($path . 'index.htm', '', $filer->chmod_file);
    }
  }

private function readdata($path) {
$path = rtrim($path, DIRECTORY_SEPARATOR );
$filer = tlocalfiler::instance();
if ($list = $filer->getdir($path)) {
$dir = 'storage/data/' . str_replace(DIRECTORY_SEPARATOR  , '/', substr($path, strlen(litepublisher::$paths->data)));
$this->adddir($dir, $filer->getchmod($path));
$dir = rtrim($dir, '/') . '/';
    $hasindex = false;
$path .= DIRECTORY_SEPARATOR ;
foreach ($list as $name => $item) {
$filename = $path . $name;
if (is_dir($filename)) {
$this->readdata($filename);
}else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)|(\.log$)/',  $name)) continue;
$this->addfile($dir . $name, file_get_contents($filename), $item['mode']);
          if (!$hasindex) $hasindex = ($name == 'index.php') || ($name == 'index.htm');
}
}
    if (!$hasindex) $this->addfile($dir . 'index.htm', '', $filer->chmod_file);
}
}
  
public function chdir($dir) {
if ($dir === $this->lastdir) return;
$this->lastdir= $dir;
if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) {
$dir = str_replace('\\', '/', $dir);
if ('/' != DIRECTORY_SEPARATOR  ) $dir = str_replace(DIRECTORY_SEPARATOR  , '/', $dir);
$dir = rtrim($dir, '/');
$root = rtrim($this->ftproot, '/');
if (strbegin($dir, $root)) $dir = substr($dir, strlen($root));
$this->filer->chdir($dir);
} else {
$this->filer->chdir($dir);
}
}

public function setdir($dir) {
$dir = trim($dir, '/');
if ($i = strpos($dir, '/')) $dir = substr($dir, $i);
if (! array_key_exists($dir, litepublisher::$_paths)) $this->error(sprintf('Unknown "%s" folder', $dir));
$this->chdir(dirname(rtrim(litepublisher::$_paths[$dir], DIRECTORY_SEPARATOR )));
}

public function check_ftp_root() {
$temp = litepublisher::$paths->data . md5uniq() . '.tmp';
file_put_contents($temp,' ');
@chmod($temp, 0666);
$filename = str_replace('\\\\', '/', $temp);
$filename = str_replace('\\', '/', $filename);
$this->filer->chdir('/');
if (($this->ftproot == '') || !strbegin($filename, $this->ftproot) || !$this->filer->exists(substr($filename, strlen($this->ftproot)))) {
$this->ftproot = $this->find_ftp_root($temp);
$this->save();
}
unlink($temp);
}

public function find_ftp_root($filename) {
$root = '';
$filename = str_replace('\\\\', '/', $filename);
$filename = str_replace('\\', '/', $filename);
if ($i = strpos($filename, ':')) {
$root = substr($filename, 0, $i);
$filename = substr($filename, $i);
}

$this->filer->chdir('/');
while (($filename != '') && !$this->filer->exists($filename)) {
if ($i = strpos($filename, '/', 1)) {
$root .= substr($filename, 0, $i);
$filename = substr($filename, $i);
} else {
return false;
}
}
return $root;
}

  public function getpartial($plugins, $theme, $lib) {
    set_time_limit(300);
$this->createarch();
    if (dbversion) $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);

$this->readdata(litepublisher::$paths->data);

    if ($lib)  {
$this->setdir('lib');
      $this->readdir('lib');
$this->setdir('js');
      $this->readdir('js');
    }

    if ($theme)  {
$this->setdir('themes');
$views = tviews::instance();
$names = array();
foreach ($views->items as $id => $item) {
if (in_array($item['themename'], $names))continue;
 $names[] = $item['themename'];
$this->readdir('themes/' . $item['themename']);
}
    }
    
    if ($plugins) {
$this->setdir('plugins');
      $plugins = tplugins::instance();
      foreach ($plugins->items as $name => $item) {
        if (@is_dir(litepublisher::$paths->plugins . $name)) {
          $this->readdir('plugins/' . $name);
        }
      }
    }
    
return $this->savearchive();
  }

  public function getfull() {
    set_time_limit(300);
$this->createarch();
    if (dbversion) $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);

$this->readdata(litepublisher::$paths->data);

$this->setdir('lib');
      $this->readdir('lib');
$this->setdir('js');
      $this->readdir('js');

$this->setdir('plugins');
      $this->readdir('plugins');

$this->setdir('themes');
      $this->readdir('themes');

return $this->savearchive();
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

private function writedata($filename, $content, $mode) {
if (strend($filename, '/index.htm') || strend($filename, '/.htaccess')) return true;
$this->hasdata = true;
$filename = substr($filename, strlen('storage/data/'));
$filename =str_replace('/', DIRECTORY_SEPARATOR, $filename);
$filename = litepublisher::$path->storage . 'newdata' . DIRECTORY_SEPARATOR . $filename;
$filer = tlocalfiler::instance();
$filer->forcedir(dirname($filename));
if (file_put_contents($filename, $content) === false) return false;
@chmod($filename, $mode);
return true;
}

private function uploadfile($filename, $content, $mode) {
      if (dbversion && $filename == 'dump.sql') {
$this->setdump($content);
return true;
      }

//spec rule for storage folder 
if (strbegin($filename, 'storage/')) {
if (!strbegin($filename, 'storage/data/')) return $this->writedata($filename, $content, $mode);
return true;
}

$dir = rtrim(dirname($filename), '/');
$this->setdir($dir);
if (!isset($this->existingfolders[$dir])) {
$this->filer->forcedir($dir);
$this->existingfolders[$dir] = true;
}

if ($this->filer->putcontent($filename, $content) === false) return false;
$this->filer->chmod($filename, $mode);
return true;
}

    public function upload(&$content, $archtype) {
    set_time_limit(300);
$this->archtype = $archtype;
$this->hasdata = false;
$this->existingfolders = array();
$this->createarchive();
    switch ($archtype) {
case 'tar':
    $this->tar->loadfromstring($content);
    foreach ($tar->files as $item) {
$this->uploadfile($item['name'],$item['file'], $item['mode']);
}
unset($this->tar);
break;      

case 'zip':
    $this->unzip->ReadData($content);
    foreach ($this->unzip->Entries as  $item) {
      if ($item->Error != 0) continue;
$this->uploadfile($item->Path . $item->Name, $item->Data, $this->filer->chmod_file);
}
unset($this->unzip);
break;

default:
$this->unknownarchive();
}
unset($this->existingfolders);
if ($this->hasdata) $this->renamedata();
    return true;
  }

private function renamedata() {
$old = litepublisher::$paths->backup . 'data-' . time();
$data =rtrim(litepublisher::$paths->data, DIRECTORY_SEPARATOR);
rename($data, $old);
rename(litepublisher::$paths->storage . 'newdata', $data);
tfiler::delete($old, true, true);
}
  
  public function createbackup(){
    $s = $this->getpartial(true, true, true);
    $filename = litepublisher::$paths->backup . litepublisher::$domain . date('-Y-m-d');
$filename .= $this->archtype == 'zip' ? '.zip' : '.tar.gz';
    file_put_contents($filename, $s);
    @chmod($filename, 0666);
    return $filename;
  }
  
}//class
?>