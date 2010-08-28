<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
$reqname = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . 'request.xml';
$HTTP_RAW_POST_DATA = file_get_contents($reqname);

litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), 
//$_SERVER['REQUEST_URI']);
'/rpc.xml');

?>