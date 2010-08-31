<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
    $updater = tupdater::instance();
$r = $updater->auto2(0);
var_dump($r);
?>