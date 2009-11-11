<?php

function tdownloadcounterInstall($self) {
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'downloadcounter.sql'));
  }

$files = tfiles::instance();
$files->deleted = $self->delete;

  $urlmap = turlmap::instance();
  $urlmap->add('/downloadcounter/'', get_class($self), 'get', null);
}

function tfilesUninstall(&$self) {
  turlmap::unsub($self);
$files->unsubscribeclass($self);
}

?>