<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminlinkswidgetInstall($self) {
$widgets = twidgets::instance();
$widgets->lock();
$widgets->add($self);
$widgets->onadminlogged = $self->adminlogged;
$widgets->unlock();
}

function tadminlinkswidgetUninstall($self) {
$widgets = twidgets::instance();
$widgets->deleteclass(get_class($self));
}

?>