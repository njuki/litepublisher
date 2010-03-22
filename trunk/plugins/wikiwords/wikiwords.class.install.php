<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twikiwordsInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager::instance();
    $manager->createtable($self->table,
    "  `id` int(10) unsigned NOT NULL auto_increment,
    `word` text NOT NULL,
    PRIMARY KEY  (`id`)");
    
    $manager->createtable($self->itemsposts->table, file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'items.posts.sql'));
  }
  
  $filter = tcontentfilter::instance();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->beforefilter = $self->filter;
  $filter->unlock();
  
  $posts = tposts::instance();
  $posts->lock();
  $posts->added = $self->postadded;
  $posts->deleted = $self->postdeleted;
  $posts->unlock();
  
  litepublisher::$classes->classes['wikiwords'] = get_class($self);
  litepublisher::$classes->save();
  
  litepublisher::$options->parsepost = true;
}

function twikiwordsUninstall($self) {
  unset(litepublisher::$classes->classes['wikiword']);
  litepublisher::$classes->save();
  
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
  tposts::unsub($self);
  if ($self->dbversion) {
    $manager = tdbmanager::instance();
    $manager->deletetable($self->table);
    $manager->deletetable($self->itemsposts->table);
  }
}

?>