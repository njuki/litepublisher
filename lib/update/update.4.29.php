<?php

function update429() {
if (isset(litepublisher::$classes->items['tmarkdownplugin'])) {
$mark = tmarkdownplugin::instance();
    $filter = tcontentfilter::instance();
    $filter->lock();
    $filter->unsubscribeclass($mark);
    $filter->onsimplefilter = $mark->filter;
    $filter->oncomment = $mark->filter;
    $filter->unlock();
  }

if (litepublisher::$options->language == 'en') {
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
$menu = tadminthemetree::instance();
$menu->title = tlocal::$data['names']['edittheme']; 
$admin->edit($menu);

$menu = tadminicons::instance();
$menu->title = tlocal::$data['common']['deficons']; 
$admin->edit($menu);

$admin->unlock();
}

}