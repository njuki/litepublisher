<?php
ob_start();
//begin config
$domain = strtolower(trim($_SERVER['HTTP_HOST']));
if (substr($domain, 0, 4) == 'www.') $domain = substr($domain, 4);
$domain = trim($domain, '.:/\,;');
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


define('secret', 'сорок тыс€ч обезъ€н в жопу сунули банан');

require_once($paths['lib'] . 'classes.php');
TClasses::Load();

require_once($paths['lib']. 'optionsclass.php');
$Options = &TOptions::Instance();

define('gmt_offset', date('Z'));
$microtime = microtime();
//end config

if (!isset($mode)) {
$Urlmap = &TUrlmap::Instance();
$Urlmap->Request(strtolower($_SERVER['HTTP_HOST']), $_SERVER['REQUEST_URI']);
}
ob_end_flush ();
?>