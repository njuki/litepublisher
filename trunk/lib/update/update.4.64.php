<?php


if (!function_exists('basemd5')) {
function basemd5($s) {
return trim(base64_encode(md5($s, true)), '=');
}
}  

function md5bin($a) {
if (strlen($a) < 32) return $a;
$result ='';
for($i=0; $i<32; $i+=2){
$result .= chr(hexdec($a[$i] . $a[$i+1]));
}
return $result;
}

function newmd5($old) {
if (strlen($old) != 32) return $old;
return trim(base64_encode(md5bin($old)), '=');
}

function update464() {
if (strlen(litepublisher::$options->data['password']) != 32) return;

/*
echo "<pre>\n";
    $old = newmd5(md5((string) $_COOKIE['admin'] . litepublisher::$secret));
    $cookie = basemd5((string) $_COOKIE['admin'] . litepublisher::$secret);
var_dump($old == $cookie);
var_dump(newmd5(litepublisher::$options->cookie ) ==  $cookie);
return;
*/

litepublisher::$site->jquery_version = '1.6.2';
litepublisher::$options->data['password'] = newmd5(litepublisher::$options->data['password']);
litepublisher::$options->cookie =  newmd5(litepublisher::$options->cookie );
litepublisher::$classes->items['tkeptcomments'] = array('kernel.comments.class.php', '');
litepublisher::$classes->items['tkeptcomments'] = array('kernel.comments.class.php', '', 'comments.form.class.php');
tstorage::savemodified();

if (dbversion) {
$man = tdbmanager::instance();
$man->alter('rawposts', "modify `hash` varchar(22) default NULL");
}

$files = tfiles::instance();
$files->lock();
if (dbversion) {
$files->loadall();
$man->alter($files->table, 'drop md5');
$man->alter($files->table, "add `hash` char(22) NOT NULL after keywords");
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
$item['password'] = newmd5($item['password']);
$item['cookie'] = newmd5($item['cookie']);
}
$users->setvalue($id, 'password', $item['password']);
$users->setvalue($id, 'cookie', $item['cookie']);
}
$users->unlock();

if (dbversion) {
$comments = tcomments::instance();
$db = $comments->db;
$db->table = $comments->rawtable;
$items = $db->res2assoc($db->query("select id, hash from $db->prefix$comments->rawtable"));
$man->alter($comments->rawtable, 'drop index hash');
$man->alter($comments->rawtable, 'drop hash');
$man->alter($comments->rawtable, "add `hash` char(22) NOT NULL");
$man->alter($comments->rawtable, "ADD INDEX `hash` (`hash`)");

foreach ($items as $item) {
if (strlen($item['hash']) == 32) $item['hash'] = newmd5($item['hash']);
$db->updateassoc($item);
}

$kept = tkeptcomments::instance();
$kept->db->delete("id <> ''");
$man->alter($kept->table, 'drop PRIMARY key');
$man->alter($kept->table, 'drop id');
$man->alter($kept->table, "add `id` char(22) NOT NULL first");
$man->alter($kept->table, "ADD PRIMARY key `id` (`id`)");

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
if (strlen($item['cookie']) == 32) $item['cookie'] = basemd5($item['cookie'] . litepublisher::$secret);
$comusers->setvalue($id, 'cookie', $item['cookie']);
}

} else {
$posts = tposts::instance();
foreach ($posts->items as  $idpost => $itempost) {
$comusers = tcomusers::instance($idpost);
$comusers->lock();
foreach ($comusers->items as $id => $item) {
if (strlen($item['cookie']) == 32) $item['cookie'] = basemd5($item['cookie'] . litepublisher::$secret);
$comusers->setvalue($id, 'cookie', $item['cookie']);
}
$comusers->unlock();
}
}

litepublisher::$options->version = '3.64';
//echo "\nupdated";
//turlmap::redir301('/admin/service/' . litepublisher::$site->q . 'update=1');
}