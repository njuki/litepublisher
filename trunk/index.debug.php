<?php
//set_time_limit(1);
error_reporting(E_ALL);
error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
//set_error_handler("exception_error_handler");
 Header( 'Cache-Control: no-cache, must-revalidate');
  Header( 'Pragma: no-cache');

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
//if (strpos($errstr, 'timezone')) return;
//    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
//if ($errno == 2 || $errno == 2048) return;
$errfile= str_replace(dirname(__file__), '', $errfile);
$s = "<pre>\n$errstr\n$errno\n$errfile\n$errline\n</pre>\n";
$s = str_replace($s, 'F:\web5\home\blogolet.ru\www\data\start.ru', '');
$s = str_replace($s, 'F:\web5\home\blogolet.ru\www', '');
echo $s;
//    throw new Exception('handle exception');
}
//set_error_handler("exception_error_handler");
//echo "<pre>\n";

class litepublisher {
public static $db;
public static $classes;
public static $options;
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

$home = dirname(__file__). DIRECTORY_SEPARATOR;
//$home = '/home/dest/www/';
self::$_paths = array(
'home' => $home,
'lib' => $home .'lib'. DIRECTORY_SEPARATOR,
'libinclude' => $home .'lib'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR,
'languages' => $home .'lib'. DIRECTORY_SEPARATOR . 'languages'. DIRECTORY_SEPARATOR,
'data' => $home . 'data'. DIRECTORY_SEPARATOR . self::$domain  . DIRECTORY_SEPARATOR,
'cache' => $home . 'cache'. DIRECTORY_SEPARATOR . self::$domain  . DIRECTORY_SEPARATOR,
'plugins' =>  $home . 'plugins' . DIRECTORY_SEPARATOR,
'themes' => $home . 'themes'. DIRECTORY_SEPARATOR,
'files' => $home . 'files' . DIRECTORY_SEPARATOR,
'backup' => $home . 'backup' . DIRECTORY_SEPARATOR,
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

litepublisher::init();

//ob_start();

if (litepublisher::$domain== 'fireflyblog.ru') {
define('dbversion' , false);
}

if (litepublisher::$debug) {
require_once(litepublisher::$paths->lib . 'data.class.php');
require_once(litepublisher::$paths->lib . 'events.class.php');
require_once(litepublisher::$paths->lib . 'items.class.php');
require_once(litepublisher::$paths->lib . 'classes.php');
require_once(litepublisher::$paths->lib . 'options.class.php');
} else {
require_once(litepublisher::$paths->lib . 'kernel.php');
}

litepublisher::$classes = tclasses::instance();
litepublisher::$options = toptions::instance();
if (!litepublisher::$options->installed) require_once(litepublisher::$paths->lib .'install' . DIRECTORY_SEPARATOR . 'install.php');
if (dbversion) litepublisher::$db = new tdatabase();
    litepublisher::$options->admincookie = litepublisher::$options->cookieenabled && litepublisher::$options->authcookie();
//tfiler::log($_SERVER['REQUEST_URI']);

litepublisher::$urlmap = turlmap::instance();
if (!defined('litepublisher_mode')) {
litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
}

/*
litepublisher::$options->cache = false;
litepublisher::$options->data['dbconfig']['prefix'] = 'litepublisherru_';
litepublisher::$options->setpassword('admin');
litepublisher::$options->save();
*/
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();
if (dbversion && !preg_match('/(^\/rpc\.xml|\/rss|\/comments\.)|(\.xml$)/', $_SERVER['REQUEST_URI'])){
echo "<pre>\n";
$man = tdbmanager::instance();
//$man->optimize();
//$man->deletealltables();
//echo  $man->performance();
//file_put_contents(litepublisher::$pathshome. "litepublisher::$domain .sql", $man->export());
}
include('lib/update/update.3.64.php');
update364();
?>