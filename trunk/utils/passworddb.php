<?php
$password = "";
define('litepublisher_mode', 'xmlrpc');
include('index.php');
   litepublisher::$options->dbconfig['password'] = base64_encode(str_rot13 ($password));
litepublisher::$options->save();
litepublisher::$options->savemodified();
echo "<pre>\n";
echo "$password\n<br>new password";
?>