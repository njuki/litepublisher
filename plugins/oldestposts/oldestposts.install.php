<?php

function TOldestPostsInstall(&$self) {$Template = &TTemplate::Instance();
  $templ = &TTemplatePost::Instance();
  $templ->Onpostscript = $self->postscript;
 }
 
function TOldestPostsUninstall(&$self) {
  $templ = &TTemplatePost::Instance();
  $templ->UnsubscribeClass($self);
 }

?>