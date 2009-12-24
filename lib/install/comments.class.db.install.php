<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentsInstall($self) {
  $manager = tdbmanager ::instance();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.sql'));
  $manager->CreateTable($self->rawtable, file_get_contents($dir .'comments.raw.sql'));
  $manager->CreateTable($self->table . 'kept', file_get_contents($dir .'comments.kept.sql'));
}

?>