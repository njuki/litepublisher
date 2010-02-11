<?php
  if (version_compare(PHP_VERSION, '5.1', '<')) {
die('Lite Publisher requires PHP 5.2 or later. You are using PHP ' . PHP_VERSION) ;
  }

//ob_start();
//begin config
define('dbversion' , 'combo'); //valid values false, 'combo', 'full'
if (!preg_match('/(www\.)?([\w\.]+)(:\d*)?/', strtolower(trim($_SERVER['HTTP_HOST'])) , $domain)) die('cant resolve domain name');
$domain = $domain[2];

$paths = array('home' => dirname(dirname(__file__)) . DIRECTORY_SEPARATOR);
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
require_once($paths['lib'] . 'kernel.php');
require_once($paths['lib'] . 'mobileclasses.php');
$classes = tmobileclasses::instance();
$options = tmobileoptions::instance();
if (!$options->installed) require_once($paths['lib'] .'install' . DIRECTORY_SEPARATOR . 'install.php');
if (dbversion) $db = new tdatabase();
//end config
$urlmap = turlmap::instance();
if (!isset($mode)) {
$urlmap->Request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
}
$options->savemodified();
//ob_end_flush ();
?>