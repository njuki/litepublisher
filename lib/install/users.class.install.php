<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusersInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'users.sql'));
    $manager->setautoincrement($self->table, 2);
  }
  
  $cron = tcron::instance();
  $cron->addnightly(get_class($self), 'optimize', null);
  
  litepublisher::$urlmap->addget('/users.htm', get_class($self));
  
  $robots = trobotstxt ::instance();
  $robots->AddDisallow('/users.htm');
}

function tusersUninstall($self) {
  turlmap::unsub($self);
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
}

?>