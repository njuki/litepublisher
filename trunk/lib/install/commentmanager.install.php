<?php

function TCommentManagerInstall(&$self) {
  $Posts= &TPosts::Instance();
  $Posts->Deleted = $self->PostDeleted;
}

function TCommentManagerUninstall(&$self) {
  TPosts::unsub($self);
  
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
}

?>