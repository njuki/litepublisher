<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcontactformInstall($self) {
  $html = tadminhtml::instance();
  $html->section = 'contactform';
  $lang = tlocal::instance('contactform');
  $self->title =  $lang->title;;
  $self->subject = $lang->subject;
  $self->success  = $html->success();
  $self->errmesg = $html->errmesg();
  $filter = tcontentfilter::instance();
  $value = $filter->phpcode;
  $filter->phpcode = false;
  $self->content = $html->form();
  $filter->phpcode = $value;
  $self->order = 10;
  
  $menus = tmenus::instance();
  $menus->add($self);
}

function tcontactformUninstall($self) {
  $menus = tmenus::instance();
  $menus->delete($self->id);
}

?>