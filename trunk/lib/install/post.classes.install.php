<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpostclassesInstall($self) {
  if (dbversion) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager = tdbmanager ::instance();
    $manager->CreateTable($self->table, file_get_contents($dir .'post.classes.sql'));
  }

litepublisher::$classes->onnewitem = $self->newitem;

$posts = tposts::instance();
$posts->lock();
$posts->added = $self->postadded;
$posts->deleted = $self->postdeleted;
$posts->unlock();
}

function tpostclassesUninstall($self) {
  if (dbversion) {
    $manager = tdbmanager ::instance();
    $manager->deletetable($self->table);
  }

litepublisher::$classes->unsubscribeclass($self);

tposts::uunsub($self);
}

?>