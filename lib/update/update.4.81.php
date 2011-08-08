<?php

function update481() {
litepublisher::$classes->add('tfakemenu', 'menu.class.php');

tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
    $id = $admin->createitem($admin->url2id('/admin/menu/'),
'editfake', 'editor', 'tadminmenumanager');
    $admin->items[$id]['title'] = tlocal::$data['menu']['addfake'];
$admin->unlock();
}