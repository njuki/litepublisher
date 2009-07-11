<?php

function TCustomWidgetInstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->WidgetDeleted = $self->WidgetDeleted;
}

function TCustomWidgetUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
}

?>