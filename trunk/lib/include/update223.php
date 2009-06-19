<?php

function Update222() {
 global $paths;
 TClasses::Lock();
TClasses::Reinstall('TXMLRPCRemoteAdmin'); 
TClasses::Unlock();
 
 @unlink($paths['lib']. 'adminquickpost.php');
@unlink($paths['languages'] . 'installru.ini');
@unlink($paths['languages'] . 'installen.ini');
@unlink($paths['libinclude'] . 'pingservices.txt');
@unlink($paths['libinclude'] . 'options.ini');
@unlink($paths['libinclude'] . 'optionsen.ini');
}

?>