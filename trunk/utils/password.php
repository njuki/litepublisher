<?php
$mode = 'pass';
include('index.php');
   $password = md5(secret. uniqid( microtime()));
   $Options->SetPassword($password);
echo "<pre>\n";
echo "$password\n<br>new password";
?>