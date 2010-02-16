<?php

function tniceditInstall($self) {
  $template = ttemplate::instance();
  $template->onadminhead = $self->onhead;
}

function tniceditUninstall($self) {
  $template = ttemplate::instance();
  $template->unsubscribeclass($self);
}

?>