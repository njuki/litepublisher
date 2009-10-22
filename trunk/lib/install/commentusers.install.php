<?php

function TCommentUsersInstall($self) {
if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'commentusers.sql'));
} else {
  $posts= tposts::instance();
  $posts->deleted = $self->postdeleted;
}

$self->options = array(
'hidelink' => false,
'redir' => true,
'nofollow' => false
);

  $urlmap = turlmap::instance();
  $urlmap->add('/comusers/', get_class($self), 'tree');
  
  $robots = TRobotstxt ::instance();
  $robots->AddDisallow('/comusers/');
}

function TCommentUsersUninstall(&$self) {
  tposts::unsub($self);
  turlmap::unsub($self);
}

?>