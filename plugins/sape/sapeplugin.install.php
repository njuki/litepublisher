<?php

function TSapePluginInstall(&$self) {$Template = &TTemplate::Instance();
$Template = &TTemplate::Instance();
$Template->Lock();
$Template->AddWidget(get_class($self), 'nocache', -1, 0);
$Template->AfterWidget = $self->AfterWidget;
$Template->Unlock();
 }
 
function TSapePluginUninstall(&$self) {
$Template = &TTemplate::Instance();
$Template->Lock();
$Template->UnsubscribeClassName(get_class($self));
  $Template->DeleteWidget(get_class($self));
$Template->Unlock();
 }

?>