<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tthemetreeInstall($self) {
 $admin= tadminmenus::instance();
$admin->lock();
$idview = $admin->url2id('/admin/views/');
$idthemes = $admin->url2id('/admin/views/themes/');
$admin->items[$idthemes]['order'] = 1;
if ($id = $admin->url2id('/admin/views/edittheme/')) {
$admin->delete($id);
    $id = $admin->createitem($idview, 'themefiles', 'admin', 'tadminthemefiles');
$admin->items[$id]['order'] = 4;
}
    $id = $admin->createitem($idview, 'edittheme', 'admin', get_class($self));
$admin->items[$id]['order'] = 2;
$admin->sort();
$admin->unlock();
}

function tthemetreeUninstall($self) {
  $admin = tadminmenus::instance();
$admin->deleteurl('/admin/views/edittheme/');
}