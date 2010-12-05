<?php
//set_time_limit(1);
error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );

 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');


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
  public static $debug = true;
  public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';
  public static $microtime;
  
  public static function init() {
    if (!preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
        self::$domain = $domain[2];
    $home = dirname(__file__) . DIRECTORY_SEPARATOR;
    $storage = $home . 'storage' . DIRECTORY_SEPARATOR;
    self::$_paths = array(
    'home' => $home,
    'lib' => $home .'lib'. DIRECTORY_SEPARATOR,
    'libinclude' => $home .'lib'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR,
    'languages' => $home .'lib'. DIRECTORY_SEPARATOR . 'languages'. DIRECTORY_SEPARATOR,
    'storage' => $storage,
    'data' => $storage . 'data'. DIRECTORY_SEPARATOR,
    'cache' => $storage . 'cache'. DIRECTORY_SEPARATOR,
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
}

try {
  litepublisher::init();
if (litepublisher::$domain== 'fireflyblog.ru') {
define('dbversion' , false);
litepublisher::$paths->data .= 'fire\\';
}

if (litepublisher::$debug) {
require_once(litepublisher::$paths->lib . 'data.class.php');
require_once(litepublisher::$paths->lib . 'events.class.php');
require_once(litepublisher::$paths->lib . 'items.class.php');
require_once(litepublisher::$paths->lib . 'classes.class.php');
require_once(litepublisher::$paths->lib . 'options.class.php');
require_once(litepublisher::$paths->lib . 'site.class.php');
} else {
require_once(litepublisher::$paths->lib . 'kernel.php');
}

tstorage::loaddata();
  litepublisher::$classes = tclasses::instance();
  litepublisher::$options = toptions::instance();
  litepublisher::$site = tsite::instance();
  if (!litepublisher::$options->installed) require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
  if (dbversion) litepublisher::$db = new tdatabase();
  litepublisher::$options->admincookie = litepublisher::$options->cookieenabled && litepublisher::$options->authcookie();
  litepublisher::$urlmap = turlmap::instance();
ttheme::clearcache();
tlocal::clearcache();

//litepublisher::$classes->delete('tajaxposteditor');
//litepublisher::$classes->add('tajaxmenueditor', 'admin.menu.ajax.class.php');

  if (!defined('litepublisher_mode')) {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
  }
  
} catch (Exception $e) {
  //echo $e->GetMessage();
litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();
//litepublisher::$urlmap->delete('/getwidget.htm');
//litepublisher::$urlmap->addget('/getwidget.htm', 'twidgets');
//litepublisher::$urlmap->addget('/admin/ajaxposteditor.htm', 'tajaxposteditor ');
/*
echo "<pre>\n";
$man = tdbmanager::instance();
echo $man->performance();
echo "\n", microtime(true) - litepublisher::$microtime, "\n";
echo tfilestorage::$time, "\n";
echo tfilestorage::$time2, "\n";
*/
//echo round(microtime(true) - litepublisher::$microtime, 2), "\n";
?>