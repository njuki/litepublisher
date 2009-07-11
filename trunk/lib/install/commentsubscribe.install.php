<?php

function TSubscribeInstall(&$self) {
  $self->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
  $self->Save();
  $CommentManager = &TCommentManager::Instance();
  $CommentManager->Lock();
  $CommentManager->Added = $self->SendMailToSubscribers;
  $CommentManager->Approved = $self->SendMailToSubscribers;
  $CommentManager->Deleted = $self->CommentDeleted;
  $CommentManager->Unlock();
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->AddGet('/comments/subscribe/', get_class($self), null);
}

function TSubscribeUninstall(&$self) {
  TUrlmap::unsub($self);
  
  $CommentManager = &TCommentManager::Instance();
  $CommentManager->UnsubscribeClass($self);
}

?>