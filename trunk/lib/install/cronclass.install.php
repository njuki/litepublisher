<?php

function TCronInstall(&$self) {
 global $paths;
 $dir = $paths['data'] . 'cron';
 @mkdir($dir, 0777);
 @chmod($dir, 0777);
 
 $self->url =  '/croncron.php?cronpassword=' . md5(secret. uniqid( microtime()) . 'cron');
 $self->Save();
 
 $Urlmap  = &TUrlmap::Instance();
 $Urlmap->Add($self->url, get_class($self), null);
}

function TCronUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>