<?php
define('litepublisher_mode', 'cron');
include('index.php');
$cron = tcron::instance();
$_GET['cronpass'] = $cron->password;
$cron->request(null);
echo "finish";
?>