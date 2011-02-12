<?php

function update423() {
$backuper = tbackuper::instance();
$backuper->data['filertype'] = 'auto';
$backuper->save();

litepublisher::$site->jquery_version = '1.5';
litepublisher::$site->save();

$parser = tthemeparser::instance();
$parser->data['replacelang'] = false;
$parser->save();

$admin = tadminmenus::instance();
if (!$admin->url2id('/admin/service/upload/')) {
$service = $admin->url2id('/admin/service/');
$admin->createitem($service, 'upload', 'admin', 'tadminservice');
}

}