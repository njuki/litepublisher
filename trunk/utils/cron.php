<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;
$cron = tcron::instance();
$_GET['cronpass'] = $cron->password;
echo "mustbe start<br>";
flush();
var_dump($cron->request(null));
echo "finish";
?>