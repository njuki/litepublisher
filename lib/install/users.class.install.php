<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusersInstall($self) {
  if ($self->dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'users.sql'));
    $manager->setautoincrement($self->table, 2);
  }
  
  $cron = tcron::instance();
  $cron->addnightly(get_class($self), 'optimize');
  
  $urlmap = turlmap::instance();
  $urlmap->add('/users.htm', get_class($self), 'get');
  
  $robots = TRobotstxt ::instance();
  $robots->AddDisallow('/users.htm');
}

function tusersUninstall($self) {
  turlmap::unsub($self);
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
}

?>