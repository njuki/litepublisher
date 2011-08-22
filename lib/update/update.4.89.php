<?php

function update489() {
litepublisher::$site->author = 'Admin';
litepublisher::$site->save();

if (litepublisher::$classes->exists('tpolls')) {
$p = tpolls::instance();
$items = $p->db->res2assoc($p->db->query('select id, votes from ' . $p->thistable));
$man = tdbmanager::instance();
$man->addenum($p->table, 'star');
$man->alter($polls->table, "add `rate` tinyint unsigned NOT NULL default '0'");
$man->alter($polls->table, "ADD INDEX `rate` (`rate`)");
$db = $p->db;
foreach ($items as  $item) {
$item['rate'] = $p->getrate(explode(',', $item['votes']));
unset($item['votes']);
$db->updateassoc($item);
}

$dir = litepublisher::$paths->plugins . 'polls' . DIRECTORY_SEPARATOR;
  $templates = parse_ini_file($dir . 'templates.ini',  true);
  $p->templateitems['star']= = $templates['item']['star'];
  $p->templates['star'] = $templates['items']]['star'];

$p->deftype = 'star';
$p->save();
}
}