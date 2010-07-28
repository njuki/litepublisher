<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadmincontextwidgetInstall($self) {
  $widgets = twidgets::instance();
  $widgets->lock();
  $self->id = $widgets->add($self);
  $widgets->onadminlogged = $self->onsitebar;
  $widgets->unlock();
  $self->save();
}

?>