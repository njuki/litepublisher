<?php

function update443() {
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
if (!$admin->url2id('/admin/views/addview/')) {
    $admin->createitem($admin->url2id('/admin/views/'), 'addview', 'admin', 'tadminviews');
}
$admin->unlock();
}
