<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tpostsInstall($self) {
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'posts.sql'));
    $manager->CreateTable('pages', file_get_contents($dir .'postspages.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'raw.sql'));
  } else {
    global $paths;
    $dir = $paths['data'] . 'posts';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
  }
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