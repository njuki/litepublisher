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

$users = tusers::instance();
$users->lock();
if (dbversion) {
$users->loadall();
$man->alter($users->table, 'drop password');
$man->alter($users->table, "add `password` varchar(22) NOT NULL after login");
$man->alter($users->table, 'drop cookie');
$man->alter($users->table, "add `cookie` varchar(22) NOT NULL after password");
}
foreach ($users->items as $id => $item) {
if (strlen($item['password']) < 30) continue;
$users->setvalue($id, 'password', newmd5($item['password']));
$users->setvalue($id, 'cookie', newmd5($item['cookie']));
}
$users->unlock();

if (dbversion) {
$comments = tcomments::instance();
$db = $comments->db;
$db->table = $comments->rawtable;
$items = $db->res2assoc($db->query("select id, has from $comments->thistable"));
$man->alter($comments->rawtable, 'drop index hash');
$man->alter($comments->rawtable, 'drop hash');
$man->alter($comments->rawtable, "add `hash` char(22) NOT NULL");
$man->alter($comments->rawtable, "add ADD INDEX `hash` (`hash`)");

foreach ($items as $item) {
$item['hash'] = newmd5($item['hash']);
$db->updateassoc($item);
}
}

}
