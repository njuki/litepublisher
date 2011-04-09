<?php

function update447() {
$admin = tadminmenus::instance();
    $admin->settitle($admin->url2id('/admin/options/redir/'), 
$admin->getadmintitle('redir'));
}
