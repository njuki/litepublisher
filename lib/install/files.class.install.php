<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfilesInstall($self) {
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'files.sql'));
    $manager->CreateTable('fileitems', file_get_contents($dir .'items.posts.sql'));
  } else {
  }
}

function tfilesUninstall($self) {
  
}

?>