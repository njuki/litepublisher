<?php


function Update241() {
$auth = &TAuthDigest::Instance();
$auth->Data['cookie'] = '';
$auth->Data['cookieenabled'] = false;
$auth->Data['cookieexpired'] = 0;
$auth->Data['xxxcheck'] = true;
$auth->Save();

TClasses::Register('TAdminLogin', 'adminlogin.php');
}
?>