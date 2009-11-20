<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tsubscribersInstall($self) {
global $classes;
if (dbversion) {
    $dbmanager = TDBManager ::instance();
    $dbmanager->CreateTable('events', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'commentsubscribe.sql'));
} else {
$posts = tposts::instance();
$posts->deleted = $self->deletepost;
}
  $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
  $self->save();

$comusers = tcomusers::instance();
$comusers->deleted = $self->deleteitem;

  $manager = $classes->commentmanager;
  $manager->lock();
  $manager->added = $self->sendmail;
  $manager->approved = $self->sendmail;
  $manager->unlock();
}

function tsubscribersUninstall(&$self) {
global $classes;
  
  $manager = $classes->commentmanager;
  $manager->unsubscribeclass($self);
}

?>