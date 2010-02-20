<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
$cron = tcron::instance();
$_GET['cronpass'] = $cron->password;
$cron->request();
echo "finish";
?>