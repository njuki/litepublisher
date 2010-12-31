<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twidgetUninstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

function twidgetsInstall($self) {
  litepublisher::$urlmap->addget('/getwidget.htm', get_class($self));
  $robot = trobotstxt::instance();
  $robot->AddDisallow('/getwidget.htm');
  
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($self));
  install_std_widgets($self);
}

function twidgetsUninstall($self) {
  turlmap::unsub($self);
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
  $sidebars = tsidebars::instance();
  
  $id = $widgets->add(tcategorieswidget::instance());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(ttagswidget::instance());
  
  $id = $widgets->add(tarchiveswidget::instance());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tlinkswidget::instance());
  $sidebars->insert($id, 'inline', 0, -1);
  
  $id = $widgets->add(tpostswidget::instance());
  $sidebars->insert($id, 'inline', 1, -1);
  
  $id = $widgets->add(tcommentswidget::instance());
  $sidebars->insert($id, true, 1, -1);
  
  $id = $widgets->add(tmetawidget::instance());
  $sidebars->insert($id, 'inline', 1, -1);
  
  $widgets->unlock();
}
?>