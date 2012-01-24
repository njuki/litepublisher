<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsubscribersInstall($self) {
  if (dbversion) {
    $dbmanager = TDBManager ::i();
    $dbmanager->CreateTable($self->table, file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'items.posts.sql'));
  }
  
  $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
  $self->save();
  
  $posts = tposts::i();
  $posts->deleted = $self->deletepost;
  
  $manager = tcommentmanager::i();
  $manager->lock();
  $manager->authordeleted = $self->deleteitem;
  $manager->added = $self->sendmail;
  $manager->approved = $self->sendmail;
  $manager->unlock();
}

function tsubscribersUninstall($self) {
  litepublisher::$classes->commentmanager->unbind($self);
  litepublisher::$classes->posts->unbind($self);
}