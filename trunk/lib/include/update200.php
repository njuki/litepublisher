<?php

function Update200() {
 global $Options;
 $Options->Lock();
 $Options->realm = 'Admin panel';
$Options->password = md5("$Options->login:$Options->realm:{$_SERVER['PHP_AUTH_PW']}");
 $Options->Unlock();
 TClasses::Register('TAuthDigest', 'authdigest.php');
}

?>