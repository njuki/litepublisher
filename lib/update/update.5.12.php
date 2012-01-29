<?php

function update512() {
litepublisher::$classes->items[tadminperms'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminperm'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminpermpassword'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminpermgroups'] = array('admin.permissions.class.php');
litepublisher::$classes->save();

if (dbversion) {
$man = tdbmanager::i();
$man->alter('posts', "add   `idperm` int unsigned NOT NULL default '0' after author");
$man->alter('tags', "add   `idperm` int unsigned NOT NULL default '0' after idview");
$man->alter('categories', "add   `idperm` int unsigned NOT NULL default '0' after idview");
}

litepublisher::$classes->add('tpostpassword', 'post.password.class.php');
}
