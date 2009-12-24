<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpingbacksInstall($self) {
  $manager = tdbmanager ::instance();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.pingbacks.sql'));
  
  $posts = tposts::instance();
  $posts->deleted = $self->postdeleted;
}

function tpingbacksUninstall($self) {
  tposts::unsub($self);
}

?>