<?php

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
  $self->Save();

$comusers = tcomusers::instance();
$comusers->deleted = $self->deleteauthor;

  $manager = $classes->commentmanager;
  $manager->lock();
  $manager->added = $self->sendmail;
  $manager->approved = $self->sendmail;
  $manager->unlock();
  
  $urlmap = turlmap::instance();
  $urlmap->add('/admin/subscribe/', get_class($self), null, 'get');
}

function tsubscribersUninstall(&$self) {
global $classes;
  turlmap::unsub($self);
  
  $manager = $classes->commentmanager;
  $manager->UnsubscribeClass($self);
}

?>