<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcontactformInstall($self) {
  $html = THtmlResource::instance();
  $html->section = 'installation';
  
  $self->order = 10;
  $self->title =  tlocal::$data['installation']['contacttitle'];
  $self->content = $html->contactform();
  $menus = tmenus::instance();
  $menus->add($self);
}

function tcontactformUninstall($self) {
  $menus = tmenus::instance();
  $menus->delete($self->id);
}

?>