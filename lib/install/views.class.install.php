<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tviewsInstall($self) {
  $self->lock();
    tlocal::loadlang('admin');
$lang = tlocal::instance('names');
  $default = $self->add($lang->default);
  $home = $self->add($lang->home);
  $home->ajax = false;
$admin = $self->add($lang->adminpanel);
  $self->unlock();
}

?>