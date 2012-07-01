<?php

function update536() {
$man = tdbmanager::i();
$man->alter('posts', 'change head   rawhead text NOT NULL');

$db = litepublisher::$db;
$from = 0;
$p = $db->prefix . 'userpage';
$u = $db->prefix . 'users';
    while ($items = $db->res2assoc($db->query("select * from $p
left join $u on $p.id = $u.id
where $u.status != 'approved'
limit $from, 100"))) {

$from += count($items);
$iditems = array();
$idurls = array();
      foreach ($items as $item) {
if ($item['idurl'] > 0) $idurls[] = (int) $item['idurl'];
$iditems[] = (int) $item['id'];
}
if (count($idurls)) {
$db->table = 'urlmap';
$db->delete(sprintf('id in (%s)', implode(',', $idurls)));
}
$db->table = 'userpage';
$db->delete(sprintf('id in (%s)', implode(',', $iditems)));
}

}