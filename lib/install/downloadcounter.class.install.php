<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloadcounterInstall($self) {
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'downloadcounter.sql'));
  }
  
  $files = tfiles::instance();
  $files->deleted = $self->delete;
  
  $urlmap = turlmap::instance();
  $urlmap->add('/downloadcounter/', get_class($self), 'get', null);
}

function tdownloadcounterUninstall(&$self) {
  turlmap::unsub($self);
  $files = tfiles::instance();
  $files->unsubscribeclass($self);
}

?>