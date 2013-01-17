<?php

function update547() {
litepublisher::$classes->add('tprefetchtxt', 'prefetch.txt.class.php');
litepublisher::$classes->add('tadminusersearch', 'admin.usersearch.class.php');

    if (litepublisher::$options->usersenabled) {
$admin = tadminmenus::i();
      $id = $admin->url2id('/admin/users/');
      $admin->createitem($id, 'search', 'admin', 'tadminusersearch');
}
}