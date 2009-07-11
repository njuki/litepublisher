<?php

function TRSSInstall(&$self) {
  global $Options;
  $Options->rss = $Options->url . '/rss/';
  $Options->rsscomments = $Options->url .  '/comments/';
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Lock();
  $Urlmap->Add('/rss/', get_class($self), 'posts');
  $Urlmap->AddFinal('comments', get_class($self));
  $Urlmap->Unlock();
  
  $CommentManager = &TCommentManager::Instance();
  $CommentManager->Changed = $self->CommentsChanged;
}

function TRSSUninstall(&$self) {
  TUrlmap::unsub($self);
  $CommentManager = &TCommentManager::Instance();
  $CommentManager->UnsubscribeClass($self);
}

?>