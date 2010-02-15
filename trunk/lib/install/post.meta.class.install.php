<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmetapostInstall($self) {
  if (dbversion) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager = tdbmanager ::instance();
    $manager->CreateTable($self->table, file_get_contents($dir .'post.meta.sql'));
  }
}

function tmetapostUninstall($self) {
}

?>