<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsingletagwidgetInstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleted = $self->widgetdeleted;
  
  $self->tags->deleted = $self->tagdeleted;
}

function tsingletagwidgetUninstall($self) {
  $self->tags->unsubscribeclass($self);
}