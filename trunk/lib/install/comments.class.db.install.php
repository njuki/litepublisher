<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentsInstall($self) {
  $manager = tdbmanager ::i();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.sql'));
  $manager->CreateTable($self->rawtable, file_get_contents($dir .'comments.raw.sql'));
}

?>