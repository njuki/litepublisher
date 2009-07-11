<?php

function TCommentUsersInstall(&$self) {
  $Posts= &TPosts::Instance();
  $Posts->Deleted = $self->PostDeleted;
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->AddFinal('authors', get_class($self));
  
  $robots = &TRobotstxt ::Instance();
  $robots->AddDisallow('/authors/');
}

function TCommentUsersUninstall(&$self) {
  TPosts::unsub($self);
  TUrlmap::unsub($self);
}

?>