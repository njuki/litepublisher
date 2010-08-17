<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twidgetUninstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

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
  litepublisher::$urlmap->onclearcache = $self->onclearcache;
}

function twidgetscacheUninstall($self) {
  litepublisher::$options->unsubscribeclass($self);
  turlmap::unsub($self);
}

function install_std_widgets($widgets) {
  $widgets->lock();
  $sitebars = tsitebars::instance();
  
  $id = $widgets->add(tcategorieswidget::instance());
  $sitebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(ttagswidget::instance());
  
  $id = $widgets->add(tarchiveswidget::instance());
  $sitebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tlinkswidget::instance());
  $sitebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tfriendswidget::instance());
  $sitebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tpostswidget::instance());
  $sitebars->insert($id, 'inline', 1, -1);
  
  $id = $widgets->add(tcommentswidget::instance());
  $sitebars->insert($id, true, 1, -1);
  
  $id = $widgets->add(tmetawidget::instance());
  $sitebars->insert($id, 'inline', 1, -1);
  
  $widgets->unlock();
}
?>