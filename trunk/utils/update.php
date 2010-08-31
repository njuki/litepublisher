<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
    $updater = tupdater::instance();
$r = $updater->update();
var_dump($r);
?>