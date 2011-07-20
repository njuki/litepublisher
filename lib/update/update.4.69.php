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

$users = tusers::instance();
if (dbversion) {
$man = tdbmanager::instance();
$man->alter($users->table, "change `url`   `website` varchar(255) NOT NULL");
$man->alter($users->table, "modify `name` text NOT NULL");
$man->alter($users->table, 'drop index status');
} else {
foreach ($users->items as $id => $item) {
$users->items[$id]['website'] = $item['url'];
unset($users->items[$id]['url']);
}
$users->save();
}
}