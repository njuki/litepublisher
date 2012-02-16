<?php

function update512() {
litepublisher::$classes->items['titem_storage'] = array('item.class.php', '');
litepublisher::$classes->items['tpostfactory'] = array('kernel.posts.php', '', 'post.class.php');
litepublisher::$classes->items['ttagfactory'] = array('kernel.posts.php', '', 'tags.common.class.php');

litepublisher::$classes->data['factories'] = array(
'tpost' => 'tpostfactory',
'ttags' => 'ttagfactory',
'tcategories' => 'ttagfactory'
);


//litepublisher::$options->savemodified();

tlocal::usefile('install');
$lang = tlocal::admin('initgroups');
$groups = tusergroups::i();
foreach ($groups->items as $id => $item) {
$groups->items[$id]['title'] = $lang->{$item['name']};
}
$groups->save();

litepublisher::$classes->add('tadmingroups', 'admin.usergroups.class.php');
litepublisher::$classes->add('tadminuseroptions', 'admin.useroptions.class.php');
litepublisher::$classes->add('tadminuserpages', 'admin.userpages.class.php');

litepublisher::$classes->add('tperm', 'permissions.class.php');
litepublisher::$classes->add('tpermgroups', 'permissions.class.php');
litepublisher::$classes->add('tpermpassword', 'permissions.password.class.php');
litepublisher::$classes->add('tsinglepassword', 'permissions.singlepassword.php');
litepublisher::$classes->add('tpasswordpage', 'passwordpage.class.php');
litepublisher::$classes->add('tperms', 'permissions.class.php');

litepublisher::$classes->add('tadminperms', 'admin.permissions.class.php');
litepublisher::$classes->add('tadminperms', 'admin.permissions.class.php');
litepublisher::$classes->add('tadminperm', 'admin.permissions.class.php');
litepublisher::$classes->add('tadminpermpassword', 'admin.permissions.class.php');
litepublisher::$classes->add('tadminpermgroups', 'admin.permissions.class.php');
litepublisher::$classes->save();
litepublisher::$options->savemodified();

if (litepublisher::$options->usersenabled) {
$adminoptions = tadminoptions::i();
$adminoptions->setusersenabled(false);
$adminoptions->setusersenabled(true);
}

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
$u = tusers::i();
foreach ($u->items as $id => $item) {
$gid = $item['gid'];
$u->items[$id]['idgroups'] = array($gid);
unset($u->items[$id]['gid']);
}
$u->save();
}

}