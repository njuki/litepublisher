<?php

function TPostContentPluginInstall(&$self) {$Template = &TTemplate::Instance();
  $templ = &TTemplatePost::Instance();
$templ->Lock();
  $templ->BeforePostContent = $self->BeforePostContent;
$templ->AfterPostContent = $self->AfterPostContent;
$templ->Unlock();
 }
 
function TPostContentPluginUninstall(&$self) {
  $templ = &TTemplatePost::Instance();
  $templ->UnsubscribeClass($self);
 }

?>