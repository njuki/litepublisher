<?php

function TSamePostsInstall(&$self) {$Template = &TTemplate::Instance();
  $templ = &TTemplatePost::Instance();
  $templ->Onpostscript = $self->postscript;
  $Posts= &TPosts::Instance();
  $Posts->Changed = $self->PostChanged;
 }
 
function TSamePostsUninstall(&$self) {
  TPosts::unsub($self);
  $templ = &TTemplatePost::Instance();
  $templ->UnsubscribeClass($self);
 }

?>