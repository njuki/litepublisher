<?php

function TPostsInstall(&$self) {
 global $paths;
 $dir = $paths['data'] . 'posts';
 @mkdir($dir, 0777);
 @chmod($dir, 0777);
 
 $Cron = &TCron::Instance();
 $Cron->Add('hour', get_class($self), 'HourCron');
}

function TPostsUninstall(&$self) {
 $Cron = &TCron::Instance();
 $Cron->RemoveClass(get_class($self));
 
 $Template = &TTemplate::Instance();
 $Template->DeleteWidget(get_class($self));
 
 //@rmdir($paths['data']. 'posts');
}

?>