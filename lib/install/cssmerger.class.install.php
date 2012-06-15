<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcssmergerInstall($self) {
  $self->lock();
  $self->items = array();
  $section = 'default';
  $self->add($section, '/js/prettyphoto/css/prettyPhoto.css');
  $self->add($section, '/js/litepublisher/css/prettyphoto.dialog.css');
  $self->unlock();
  
  $template = ttemplate::i();
  $template->addtohead('<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />');
  
  $updater = tupdater::i();
  $updater->onupdated = $self->save;
}

function tcssmergerUninstall($self) {
  $updater = tupdater::i();
  $updater->unbind($self);
}