<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackuper extends tevents {
private $__filer;
private $tar;
private $zip;
private $archtype;
public  $filertype;

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
$this->archtype = 'zip';
$this->filertype = self::getprefered();
  }

public function __destruct() {
unset($this->__filer, $this->tar, $this->zip);
parent::__destruct();
}

public function unknown_archive() {
$this->error('Unknow archive type ' $this->archtype);
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
if (isset($this->__filer) return $this->__filer;
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
$result = new tlocalfiler();
$result->chmod_file = 0666;
$result->chmod_dir = 0777;
}

$this->__filer = $result;
return $result;
}

public function createarch() {
switch ($this->archtype) {
case 'tar':
self::include_tar();
   $this->tar = new tar();
break;

case 'zip':
self::include_zip();
$this->zip = new zip();
break;

default:
$this->unknown_archive();
}
}

public function savearch() {
switch ($this->archtype) {
case 'tar':
return $this->tar->savetostring(true);

case 'zip':
return $this->zip->file();

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

  public function  readdir($path) {
$filer = $this->getfiler();
    $path  = rtrim($path, '/');
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
    if (!$hasindex) $this->addfile($$path . 'index.htm', '', $filer->chmod_file);
    }
  }

public function chdir($dir) {
if (($this->filertype == 'ftp') || ($this->filertype == 'socket')) {
if ('/' != DIRECTORY_SEPARATOR  ) $dir = str_replace(DIRECTORY_SEPARATOR  , '/', $dir);
$dir = rtrim($dir, '/');
$root = rtrim($this->ftproot, '/');
if (strbegin($dir, $root)) $dir = substr($dir, strlen($$root));
$this->filer->chdir($dir);
} else {
$this->filer->chdir($dir);
}
}
  
  public function getpartial($plugins, $theme, $lib) {
    set_time_limit(300);
$this->createarch();
    if (dbversion) $this->addfile('dump.sql', $this->getdump(), $this->filer->chmod_file);

$this->chdir(litepublisher::$paths->storage);
$this->readdir('storage/data');

    if ($lib)  {
$this->chdir(litepublisher::$paths->lib);
      $this->readdir('lib');
$this->chdir(litepublisher::$paths->js);
      $this->readdir('js');
    }

    if ($theme)  {
$this->chdir(litepublisher::$paths->themes);
$views = tviews::instance();
$names = array();
foreach ($views->items as $id => $item) {
if (in_array($item['themename'], continue;
$names)) $names[] = $item['themename'];
$this->readdir('themes/' . $item['themename']);
}
    }
    
    if ($plugins) {
      $plugins = tplugins::instance();
      foreach ($plugins->items as $name => $item) {
$this->chdir(litepublisher::$paths->plugins);
        if (@is_dir(litepublisher::$paths->plugins . $name)) {
          $this->readdir('plugins/' . $name);
        }
      }
    }
    
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
  
  public function upload(&$content) {
    set_time_limit(300);
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
    set_time_limit(300);
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
    //$this->readdir($tar, litepublisher::$paths->files, '', 'files/');
    
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