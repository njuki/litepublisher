<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusersInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'users.sql'));
    $manager->setautoincrement($self->table, 2);
  }
  
  $cron = tcron::i();
  $cron->addnightly(get_class($self), 'optimize', null);
}

function tusersUninstall($self) {
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
}

?>