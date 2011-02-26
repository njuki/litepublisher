<?php

function update431() {
litepublisher::$classes->add('ttablecolumns', 'htmlresource.class.php');
litepublisher::$classes->add('tadmincommoncomments',  'admin.common.comments.php');
litepublisher::$site->jquery_version = '1.5.1';
litepublisher::$site->save();

$admin = tadminmenus::instance();
$admin->lock();
if (!$admin->url2id('/admin/service/upload/')) {
$service = $admin->url2id('/admin/service/');
$admin->createitem($service, 'upload', 'admin', 'tadminservice');
}

if ($id = $admin->url2id('/admin/downloaditems/')) {
$admin->items[$id]['group'] = 'editor';
foreach ($admin->items as $iditem => $item) {
if ($id == $item['parent']) $admin->items[$iditem]['group'] = 'editor';
}
}

if (litepublisher::$options->language == 'en') {
if ($id = $admin->url2id('/admin/views/edittheme/')) {
$admin->items[$id]['title'] = tlocal::$data['names']['edittheme']; 
}
if ($id = $admin->url2id('/admin/files/icon/')) {
$admin->items[$id]['title'] = tlocal::$data['common']['deficons']; 
}
}

if (isset(litepublisher::$classes->items['ttickets'])) {
$admin->onexclude = ttickets::instance()->onexclude;
}

$admin->unlock();

}