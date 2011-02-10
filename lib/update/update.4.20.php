<?php

function update420() {
$admin = tadminmenus::instance();
$admin->lock();
$service = $admin->url2id('/admin/service/');
$admin->createitem($service, 'upload', 'admin', 'tadminservice');
$admin->unlock();

$groups = tusergroups::instance();
  $groups->add('subeditor', '/admin/posts/');
$groups->save();
}