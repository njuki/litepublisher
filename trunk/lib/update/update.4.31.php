<?php

function update431() {
litepublisher::$site->jquery_version = '1.5.1';
litepublisher::$site->save();

$admin = tadminmenus::instance();
if (!$admin->url2id('/admin/service/upload/')) {
$service = $admin->url2id('/admin/service/');
$admin->createitem($service, 'upload', 'admin', 'tadminservice');
}

}