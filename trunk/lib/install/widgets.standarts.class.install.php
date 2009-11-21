<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tstdwidgetsInstall($self) {
$self->lock();
$widgets = twidgets::instance();
$widgets->lock();
  $widgets->deleted= $self->widgetdeleted;
//sitebar 1
$self->add('categories', true, 0);
$self->add('archives', true, 0);
$self->add('links', true, 0);
$self->add('friends', true, 0);
//sitebar 2
$self->add('posts', true, 1);
$self->add('comments', true, 1);
$self->add('meta', true, 1);

$widgets->unlock();
$self->unlock();
}

function tstdwidgetsUninstall($self) {
$self->lock();
$widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
$self->unlock();
}

?>