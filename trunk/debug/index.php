<?php
  @Header( 'Cache-Control: no-cache, must-revalidate');
  @Header( 'Pragma: no-cache');

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
//    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
//if ($errno == 2 || $errno == 2048) return;
$errfile= str_replace(dirname(__file__), '', $errfile);
echo "$errstr<br>
 $errno<br>
$errfile<br>
 $errline<br>
";
//echo "<pre>\n";
//    throw new Exception('trace');
}
//set_error_handler("exception_error_handler");
//echo "<pre>\n";
define('debug', '');
//ob_start();
//begin config

if (!preg_match('/(www\.)?([\w\.]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
$domain = $domain[2];
if ($domain== 'fireflyblog.ru') {
define('dbversion' , false);
} else {
define('dbversion' , 'combo');
}
$paths = array('home' => dirname(__file__). DIRECTORY_SEPARATOR);
$paths['lib'] = $paths['home'] .'lib'. DIRECTORY_SEPARATOR;
$paths['libinclude'] = $paths['lib'] . 'include'. DIRECTORY_SEPARATOR;
$paths['languages'] = $paths['lib'] . 'languages'. DIRECTORY_SEPARATOR;
$paths['plugins'] =  $paths['home'] . 'plugins' . DIRECTORY_SEPARATOR;
$paths['themes'] = $paths['home'] . 'themes'. DIRECTORY_SEPARATOR;
$paths['data'] = $paths['home'] . 'data'. DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
$paths['cache'] = $paths['home'] . 'cache'. DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
$paths['files'] = $paths['home'] . 'files' . DIRECTORY_SEPARATOR;
$paths['backup'] = $paths['home'] . 'backup' . DIRECTORY_SEPARATOR;
$paths['js'] = $paths['home'] . 'js' . DIRECTORY_SEPARATOR;

define('secret', 'сорок тыс€ч обезъ€н в жопу сунули банан');
$microtime = microtime();
if (!defined('debug')) {
require_once($paths['lib'] . 'kernel.php');
} else {
require_once($paths['lib'] . 'data.class.php');
require_once($paths['lib'] . 'events.class.php');
require_once($paths['lib'] . 'items.class.php');
require_once($paths['lib'] . 'classes.php');
require_once($paths['lib'] . 'options.class.php');
}
$classes = tclasses::instance();
$options = toptions::instance();
if (!$options->installed) require_once($paths['lib'] .'install' . DIRECTORY_SEPARATOR . 'install.php');

if (dbversion) $db = new tdatabase();
//end config

$urlmap = turlmap::instance();
if (!defined('litepublisher_mode')) {
$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
}
$options->cache = false;
$options->savemodified();

    if (!empty($options->errorlog) && (defined('debug') || $options->echoexception || $urlmap->admin)) {
      echo $options->errorlog;
} elseif (dbversion && !preg_match('/^\/rpc\.xml|\/rss|\/comments\./', $_SERVER['REQUEST_URI'])){
echo "<pre>\n";
$man = tdbmanager::instance();
echo  $man->performance();
//file_put_contents($paths['home']. "$domain.sql", $man->export());
}
//
?>