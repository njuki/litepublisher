<?php

function TArchivesInstall(&$self) {
 $posts = &TPosts::Instance();
 $posts->Changed = $self->PostsChanged;
 $self->PostsChanged();
}

function TArchivesUninstall(&$self) {
 TUrlmap::unsub($self);
 TPosts::unsub($self);
 
 $Template = &TTemplate::Instance();
 $Template->DeleteWidget(get_class($self));
}

?>