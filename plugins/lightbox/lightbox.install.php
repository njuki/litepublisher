<?php

function TLightboxInstall(&$self) {$Template = &TTemplate::Instance();
$Template = &TTemplate::Instance();
$Template->Onhead = $self->Onhead;

$self->posts = true;
}

function TLightboxUninstall(&$self) {
$Template = &TTemplate::Instance();
  $Template->UnsubscribeClass($self);

$filter = &TContentFilter::Instance();
  $filter->UnsubscribeClass($self);
 }

?>