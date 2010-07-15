<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twidgetsInstall($self) {
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($self));
install_std_widgets($self);
}

function twidgetsUninstall($self) {
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->deleteclass(get_class($self));
}

function twidgetscacheInstall($self) {
litepublisher::$options->onsave = $self->savemodified;
}

function twidgetscacheUninstall($self) {
litepublisher::$options->unsubscribeclass($self);
}

function install_std_widgets($widgets) {
$widgets->lock();
$sitebars = tsitebars::instance();

$id = $widgets->add(tcategorieswidget::instance());
$sitebars->insert($id, true, 0, -1);

$id = $widgets->add(tarchiveswidget::instance());
$sitebars->insert($id, true, 0, -1);

$id = $widgets->add(tlinkswidget::instance());
$sitebars->insert($id, true, 0, -1);

$widgets->unlock();
}
?>