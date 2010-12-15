<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
   $password = md5uniq();
   litepublisher::$options->changepassword($password);
litepublisher::$options->savemodified();
echo "<pre>\n";
echo "$password\n<br>new password";
?>