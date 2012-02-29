<?php

function update516() {
$views = tviews::i();
foreach ($views->items as &$item) {
$item['class'] = 'tview';
}
$views->save();

if (litepublisher::$options->usersenabled) {
$lang = tlocal::admin('users');
    $menus = tadminmenus::i();
      $menus->createitem($menus->url2id('/admin/posts/'),
'authorpage', 'author', 'tadminuserpages');
$menus->save();
}
}