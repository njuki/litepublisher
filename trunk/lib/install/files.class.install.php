<?php

function tfilesInstall(&$self) {
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'files.sql'));
    $manager->CreateTable('fileitems', file_get_contents($dir .'fileitems.sql'));
  } else {

}

function tfilesUninstall(&$self) {

}

?>