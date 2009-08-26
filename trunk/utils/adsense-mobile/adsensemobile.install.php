<?php

function TAdsensemobileInstall(&$self) {
$Template = TTemplate::Instance();
  $Template->Onbody = $self->body;
}

function TAdsensemobileUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->UnsubscribeClass(get_class($self));
}

?>