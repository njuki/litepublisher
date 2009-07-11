<?php

function TCommonTagsInstall(&$self) {
  if ('TCommonTags' == get_class($self)) return;
  $Posts= &GetInstance($self->postsclass);
  $Posts->Lock();
  $Posts->Added = $self->PostEdit;
  $Posts->Edited = $self->PostEdit;
  $Posts->Deleted = $self->PostDeleted;
  $Posts->Unlock();
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->AddNode($self->PermalinkIndex, get_class($self), 0);
}

function TCommonTagsUninstall(&$self) {
  $posts = &GetInstance($self->postsclass);
  $posts->UnsubscribeClass($self);
  
  TUrlmap::unsub($self);
  
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
}

?>