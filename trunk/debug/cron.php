<?php
$mode = 'cron';
include('index.php');
$cron = tcron::instance();
$cron->request();
?>