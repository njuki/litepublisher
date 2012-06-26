<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

if (version_compare(PHP_VERSION, '5.1', '<')) {
  die('Lite Publisher requires PHP 5.1 or later. You are using PHP ' . PHP_VERSION) ;
}

class litepublisher {
  public static $db;
  public static $storage;
  public static $classes;
  public static $options;
  public static $site;
  public static $urlmap;
  public static $paths;
  public static $_paths;
  public static $domain;
  public static $debug = false;
  public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';
  public static $microtime;
  
  public static function init() {
    if (defined('litepublisher_mode') && (litepublisher_mode == 'debug')) litepublisher::$debug = true;
    if (!preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
    self::$domain = $domain[2];
    $home = dirname(__file__) . DIRECTORY_SEPARATOR;
    $storage = $home . 'storage' . DIRECTORY_SEPARATOR;
    self::$_paths = array(
    'home' => $home,
    'lib' => $home .'lib'. DIRECTORY_SEPARATOR,
    'data' => $storage . 'data'. DIRECTORY_SEPARATOR,
    'cache' => $storage . 'cache'. DIRECTORY_SEPARATOR,
    'libinclude' => $home .'lib'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR,
    'languages' => $home .'lib'. DIRECTORY_SEPARATOR . 'languages'. DIRECTORY_SEPARATOR,
    'storage' => $storage,
    'backup' => $storage . 'backup' . DIRECTORY_SEPARATOR,
    'plugins' =>  $home . 'plugins' . DIRECTORY_SEPARATOR,
    'themes' => $home . 'themes'. DIRECTORY_SEPARATOR,
    'files' => $home . 'files' . DIRECTORY_SEPARATOR,
    'js' => $home . 'js' . DIRECTORY_SEPARATOR
    );
    
    self::$paths = new tpaths();
    self::$microtime = microtime(true);
  }
  
}

class tpaths {
public function __get($name) { return litepublisher::$_paths[$name]; }
public function __set($name, $value) { litepublisher::$_paths[$name] = $value; }
public function __isset($name) { return array_key_exists($name, litepublisher::$_paths); }
}

try {
  if (class_exists('Memcache')) {
    $memcache =  new Memcache;
    $memcache->connect('127.0.0.1', 11211);
} else {
$memcache = null;
}
  
  litepublisher::init();
  if (litepublisher::$debug) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
    require_once(litepublisher::$paths->lib . 'data.class.php');
    require_once(litepublisher::$paths->lib . 'events.class.php');
    require_once(litepublisher::$paths->lib . 'items.class.php');
    require_once(litepublisher::$paths->lib . 'classes.class.php');
    require_once(litepublisher::$paths->lib . 'options.class.php');
    require_once(litepublisher::$paths->lib . 'site.class.php');
  } else {
if ($memcache) {
$filename = litepublisher::$paths->lib . 'kernel.php';
if ($s = $memcache->get($filename)) {
eval('?>' . $s);
} else {
      $s = file_get_contents($filename);
      eval('?>' . $s);
$memcache->set($filename, $s, false, 3600);
}
} else {
    require_once(litepublisher::$paths->lib . 'kernel.php');
}
  }
  
  define('dbversion', true);

    tfilestorage::$memcache =  $memcache;
if (!tstorage::loaddata()) {
if (file_exists(litepublisher::$paths->data . 'storage.php') && filesize(litepublisher::$paths->data . 'storage.php')) die('Storage not loaded');
  //if (!litepublisher::$options->installed) require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
}

  litepublisher::$classes = tclasses::i();
  litepublisher::$options = toptions::i();
  litepublisher::$site = tsite::i();
  //if (!litepublisher::$options->installed) require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
  litepublisher::$db = tdatabase::i();
  
  litepublisher::$urlmap = turlmap::i();
  if (!defined('litepublisher_mode')) {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
  }
} catch (Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();