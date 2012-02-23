<?php

function update514() {
litepublisher::$classes->add('tcssmerger', 'cssmerger.class.php');
litepublisher::$classes->add('tadmincssmerger', 'admin.cssmerger.class.php');

$lang = tlocal::admin('views');
    $menus = tadminmenus::i();
    $menus->createitem($menus->url2id('/admin/views/'),
'cssmerger', 'admin', 'tadmincssmerger');
$menus->save();
}