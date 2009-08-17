<?php

function TNiceditInstall(&$self) {$Template = &TTemplate::Instance();
$Template = &TTemplate::Instance();
$Template->OnAdminHead = $self->Onhead;
}

function TNiceditUninstall(&$self) {
$Template = &TTemplate::Instance();
  $Template->UnsubscribeClass($self);
}

?>