<?php

function update469() {
$url = '/users.htm';
if (dbversion) {
$item = litepublisher::$urlmap->db->finditem('url = ' . dbquote($url));
litepublisher::$urlmap->db->setvalue($item['id'], 'type', 'get');
} else {
litepublisher::$urlmap->items[$url]['type'] = 'get';
litepublisher::$urlmap->save();
}

litepublisher::$classes->add('tuserpages', 'users.pages.class.php');

$users = tusers::instance();
$users->lock();
$users->loadall();
$pages = tuserpages::instance();
$pages->lock();
foreach ($users->items as $id => $item) {
$pages->add($id, $item['name'], $item['email'], $item['url']);
if (dbversion) {
$pages->updateassoc(array(
'id' => $id,
'idurl' => 0,
'idview' => 1,
'expired' => $item['expired'],
'registered' => $item['registered'],
'ip' => $item['ip'],
'avatar' => $item['avatar']
));
} else {
unset($item['name'], $item['email'], $item['url'], $item['expired'], $item['registered'], $item['ip'], $item['avatar']);
$users->items[$id] = $item;
}
}
$pages->unlock();

if (dbversion) {
$man = tdbmanager::instance();
$man->alter($users->table, 'drop index status');
$man->alter($users->table, "drop name");
$man->alter($users->table, "drop email");
$man->alter($users->table, "drop url");
$man->alter($users->table, "drop expired");
$man->alter($users->table, "drop registered");
$man->alter($users->table, "drop ip");
$man->alter($users->table, "drop avatar");
}
$users->unlock();
}