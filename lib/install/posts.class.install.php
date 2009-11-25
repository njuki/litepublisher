<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tpostsInstall($self) {
  if (dbversion) {
    $manager = tdbmanager ::instance();
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
  $Cron = tcron::instance();
  $Cron->add('hour', get_class($self), 'HourCron');
}

function tpostsUninstall($self) {
  $Cron = tcron::instance();
  $Cron->deleteclass(get_class($self));
  
$widgets = twidgets::instance();
$widgets->deleteclass($clf);  
  //@rmdir($paths['data']. 'posts');
}

?>