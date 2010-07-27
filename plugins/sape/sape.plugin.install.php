<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsapepluginInstall($self) {
  $self->data['optcode'] = md5uniq();
  $self->save();
  
$widgets = twidgets::instance();
  $widgets->lock();
$id = $widgets->add($self);
$sitebars = tsitebars::instance();
$sitebars->add($id);
  $widgets->onsitebar= $self->onsitebar;
  $widgets->unlock();
  
  litepublisher::$urlmap->clearcache();
}

function tsapepluginUninstall($self) {
  
}

?>