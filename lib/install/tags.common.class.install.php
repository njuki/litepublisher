<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommontagsInstall($self) {
  if ('tcommontags' == get_class($self)) return;
  
  $posts= tposts::instance();
  $posts->lock();
  $posts->added = $self->postedited;
  $posts->edited = $self->postedited;
  $posts->deleted = $self->postdeleted;
  $posts->unlock();
  
  $urlmap = turlmap::instance();
  $urlmap->add("/$self->PermalinkIndex/", get_class($self), 0);
  
  if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->createtable($self->table, file_get_contents($dir .'tags.sql'));
    $manager->createtable($self->itemsposts->table, file_get_contents($dir .'items.posts.sql'));
    $manager->createtable($self->contents->table, file_get_contents($dir .'tags.content.sql'));
  } else {
    $dir = litepublisher::$paths->data . $self->basename;
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
  }
  
}

function TCommonTagsUninstall(&$self) {
  tposts::unsub($self);
  turlmap::unsub($self);
  
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>