<?php

function md5bin($a) {
$result ='';
for($i=0; $i<32; $i+=2){
$result .= chr(hexdec($a[$i] . $a[$i+1]));
}
return $result;
}

function newmd5($old) {
return trim(base64_encode(md5bin($old)), '=');
}

function update464() {
litepublisher::$site->jquery_version = '1.6.2';
litepublisher::$options->data['password'] = newmd5(litepublisher::$options->data['password']);
litepublisher::$options->cookie =  newmd5(litepublisher::$options->cookie );
tstorage::save();

if (dbversion) {
$man = tdbmanager::instance();
$man->alter('rawposts', 'change `hash` varchar(22) default NULL");
}

$files = tfiles::instance();
$files->lock();
if (dbversion) {
$files->loadall();
$man->alter($files->table, 'drop md5');
$man->alter($files->table, "add `hash` char(22) NOT NULL after login");
}
foreach ($files->items as $id => $item) {
if (isset($item['hash'])) continue;
$item['hash'] = newmd5($item['md5']);
unset($files->items[$id]['md5']);
$files->setvalue($id, 'hash', newmd5($item['md5']));
}
$files->unlock();

$users = tusers::instance();
$users->lock();
if (dbversion) {
$users->loadall();
$man->alter($users->table, 'drop password');
$man->alter($users->table, "add `password` char(22) NOT NULL after login");
$man->alter($users->table, 'drop cookie');
$man->alter($users->table, "add `cookie` char(22) NOT NULL after password");
}
foreach ($users->items as $id => $item) {
if (strlen($item['password']) == 32) {
$item['password'] = newmd5($item['password'];
$item['cookie'] = newmd5($item['cookie'];
}
$users->setvalue($id, 'password', $item['password']);
$users->setvalue($id, 'cookie', $item['cookie']);
}
$users->unlock();

if (dbversion) {
$comments = tcomments::instance();
$db = $comments->db;
$db->table = $comments->rawtable;
$items = $db->res2assoc($db->query("select id, hash from $comments->thistable"));
$man->alter($comments->rawtable, 'drop index hash');
$man->alter($comments->rawtable, 'drop hash');
$man->alter($comments->rawtable, "add `hash` char(22) NOT NULL");
$man->alter($comments->rawtable, "ADD INDEX `hash` (`hash`)");

foreach ($items as $item) {
if (strlen($item['hash']) == 32) $item['hash'] = newmd5($item['hash']);
$db->updateassoc($item);
}

$kept = tkeptcomments::instance();
$kept->delete("id <> ''");
$man->alter($kept->table, 'drop index id');
$man->alter($kept->table, 'drop id');
$man->alter($kept->table, "add `id` char(22) NOT NULL first");
$man->alter($kept->table, "ADD PRIMARY INDEX `id` (`id`)");

if (litepublisher::$classes->exists('tpolls')) {
$polls = tpolls::instance();

$db = $polls->getdb($polls->userstable);
$items = $db->res2assoc($db->query("select id, cookie from $db->prefix$polls->userstable"));
$man->alter($polls->userstable, 'drop index cookie');
$man->alter($polls->userstable, 'drop cookie');
$man->alter($polls->userstable, "add `cookie` char(22) NOT NULL ");
$man->alter($polls->userstable, "ADD INDEX `cookie` (`cookie`)");
foreach ($items as  $item) {
if (strlen($item['cookie']) == 32) $item['cookie'] = newmd5($item['cookie']);
$db->updateassoc($item);
}
}

$comusers = tcomusers::instance();
$comusers->loadall();
$man->alter($comusers->table, 'drop cookie');
$man->alter($comusers->table, "add `cookie` char(22) NOT NULL after url");

foreach ($comusers->items as $id => $item) {
if (strlen($item['cookie']) == 32) $item['cookie'] = newmd5($item['cookie']);
$comusers->setvalue($id, 'cookie', $item['cookie']);
}

} else {
$posts = tposts::instance();
foreach ($posts->items as  $idpost => $itempost) {
$comusers = tcomusers::instance($idpost);
$comusers->lock();
foreach ($comusers->items as $id => $item) {
if (strlen($item['cookie']) == 32) $item['cookie'] = newmd5($item['cookie']);
$comusers->setvalue($id, 'cookie', $item['cookie']);
}
$comusers->unlock();
}
}
}
