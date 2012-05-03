<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
   $password = md5uniq();
   litepublisher::$options->changepassword($password);
litepublisher::$options->savemodified();
echo "<pre>\n";
echo litepublisher::$options->email;
echo "\n$password\n<br>new password";
