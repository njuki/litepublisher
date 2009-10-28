<?php

function tcustomwidgetInstall($self) {
$widgets = twidgets::instance();
  $widgets->deleted= $self->widgetdeleted;
}

function tcustomwidgetUninstall($self) {
$widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>