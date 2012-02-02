<?php

function update512() {
if (dbversion) {
$man = tdbmanager::i();
$man->alter('posts', "add   `idperm` int unsigned NOT NULL default '0' after author");
$man->alter('tags', "add   `idperm` int unsigned NOT NULL default '0' after idview");
$man->alter('categories', "add   `idperm` int unsigned NOT NULL default '0' after idview");

$u = tusers::i();
$dir = litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR;
    $man->CreateTable($u->grouptable, file_get_contents($dir .'usersgroups.sql'));
$man->alter($u->table, "add `idgroups` text NOT NULL");
$items = $u->db->res2assoc($u->db->query("select id, gid from $u->thistable"));
foreach ($items as $item) {
$u->db->setvalue($item['id'], 'idgroups', $item['gid']);
$u->getdb($u->grouptable)->add(array(
'iduser' => $item['id'],
'idgroup' => $item['gid']
));
}

$man->alter($u->table, "drop gid"); 
} else {
$u = tusers::i();}
foreach ($u->items as $id => $item) {
$gid = $item['gid'];
$u->items[$id]['idgroups'] = array($gid);
unset($u->items[$id]['gid']);
}
$u->save();
}

tlocal::usefile('install');
$lang = tlocal::i('initgroups');
$groups = tusergroups::i();
foreach ($groups->items as $id => $item) {
$groups->items[$id]['title'] = $lang->{$item['name']};
}
$groups->save();

litepublisher::$classes->items['tadminusergroups'] = array('admin.usergroups.class.php', '');
litepublisher::$classes->items[tadminperms'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminperm'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminpermpassword'] = array('admin.permissions.class.php', '');
litepublisher::$classes->items['tadminpermgroups'] = array('admin.permissions.class.php');
litepublisher::$classes->save();


litepublisher::$classes->add('tpostpassword', 'post.password.class.php');
}
