<?php

function update440() {
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
if ($id = $admin->url2id('/admin/views/defaults/')) {
$admin->items[$id]['title'] = tlocal::$data['names']['defaults'];
}

    $admin->createitem($admin->url2id('/admin/views/'), 'group', 'admin', 'tadminviews');
$admin->unlock();
}
