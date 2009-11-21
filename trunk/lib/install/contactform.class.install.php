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

    $menu = tmenu::instance();
$menu->lock();
    $item = new tmenuitem();
    $item->order = 10;
    $item->title =  tlocal::$data['installation']['contacttitle'];
$item->content = $html->contactform();
$menu->add($item);
$menu->onprocessform = $self->processform;
$menu->unlock();
  }
  
function tcontactformUninstall($self) {
    $menu = tmenu::instance();
$menu->unsubscribeclass($self);
}

?>