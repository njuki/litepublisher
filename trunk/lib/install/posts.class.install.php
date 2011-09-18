<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpostsInstall($self) {
  if ('tposts' != get_class($self)) return;
  if (dbversion) {
    $manager = tdbmanager ::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'posts.sql'));
    $manager->CreateTable('pages', file_get_contents($dir .'postspages.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'raw.sql'));
  } else {
    $dir = litepublisher::$paths->data . 'posts';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
  }
  $Cron = tcron::i();
  $Cron->add('hour', get_class($self), 'HourCron');
}

function tpostsUninstall($self) {
  if ('tposts' != get_class($self)) return;
  $Cron = tcron::i();
  $Cron->deleteclass(get_class($self));
  
  $widgets = twidgets::i();
  $widgets->deleteclass($self);
  //@rmdir(litepublisher::$paths->data . 'posts');
}

?>