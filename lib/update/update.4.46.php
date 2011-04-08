<?php

function update446() {
litepublisher::$classes->add('tadminredirector', 'admin.redirect.class.php');

$admin = tadminmenus::instance();
    $admin->createitem($admin->url2id('/admin/options/'), 
'redir', 'admin', 'tadminredirector');
}