<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
set_time_limit(120);
    $updater = tupdater::instance();
$r = $updater->auto2(0);
var_dump($r);
?>