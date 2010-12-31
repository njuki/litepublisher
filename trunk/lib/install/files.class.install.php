<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfilesInstall($self) {
  if (dbversion) {
    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->createtable($self->table, file_get_contents($dir .'files.sql'));
    $manager->createtable($self->itemsposts->table, file_get_contents($dir .'items.posts.sql'));
  }
  
  $posts= tposts::instance();
  $posts->lock();
  $posts->added = $self->postedited;
  $posts->edited = $self->postedited;
  $posts->deleted = $self->itemsposts->deletepost;
  $posts->unlock();
}

function tfilesUninstall($self) {
  tposts::unsub($self);
  tposts::unsub($self->itemsposts);
}

?>