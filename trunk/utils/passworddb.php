<?php
$password = "";
define('dbversion', false);
define('litepublisher_mode', 'xmlrpc');
try {
include('index.php');
    } catch (Exception $e) {
echo "error: ";
echo $e->GetMessage();
}
   litepublisher::$options->dbconfig['password'] = base64_encode(str_rot13 ($password));
litepublisher::$options->save();
litepublisher::$options->savemodified();
echo "<pre>\n";
echo "$password\n<br>new password";