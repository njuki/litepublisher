<?php

function TSamePostsExcerptInstall(&$self) {$Template = &TTemplate::Instance();
  $templ = &TTemplatePost::Instance();
  $templ->Onpostscript = $self->postscript;
  $Posts= &TPosts::Instance();
  $Posts->Changed = $self->PostChanged;
 }
 
function TSamePostsExcerptUninstall(&$self) {
  TPosts::unsub($self);
  $templ = &TTemplatePost::Instance();
  $templ->UnsubscribeClass($self);
 }

?>