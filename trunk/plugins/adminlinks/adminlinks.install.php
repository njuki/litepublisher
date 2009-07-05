<?php

function TAdminLinksPluginInstall(&$self) {
$pt = &TTemplatePost::Instance();
$pt->Onpostscript = $self->postscript;
}

function TAdminLinksPluginUninstall(&$self) {
$pt = &TTemplatePost::Instance();
$pt->UnsubscribeClass($self);
}

?>