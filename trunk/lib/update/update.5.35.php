<?php

function update535() {
if (litepublisher::$classes->exists('tregservices')) {
  $rs = tregservices::i();
//litepublisher::$urlmap->delete('/odnoklassniki-oauth2callback.php');
  litepublisher::$classes->add('todnoklassnikiservice', 'odnoklassniki.service.php', 'regservices');

$rs->lock();
$rs->data['dirname'] = 'regservices';
$rs->add(todnoklassnikiservice::i());
$rs->unlock();

//create tmp table copy data ant after rename
    $names =implode("', '", array_keys($rs->items));
    $man = tdbmanager::i();
//$row=$man->fetchnum($man->query("show create table `$man->regservices`"));
//dumpstr($row[1]);
$tmp = 'tmp_regservices';
$db = litepublisher::$db;
$man->createtable($tmp,
    "id int unsigned NOT NULL default 0,
    service enum('$names') default 'google',
    uid varchar(22) NOT NULL default '',
    
    key `id` (`id`),
    KEY (`service`, `uid`)
    ");

    $from = 0;
    while ($items = $db->res2assoc($db->query("select * from $db->regservices limit $from, 500"))) {
$from += count($items);
$db->table = $tmp;
      foreach ($items as $item) {
if (strlen($item['uid']) >= 22) $item['uid'] = basemd5($item['uid']);
$db->insert_a($item);
}
}

$man->deletetable('regservices');
$db->query("rename table $db->prefix$tmp to $db->regservices");
}
}