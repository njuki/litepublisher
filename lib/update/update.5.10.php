<?php

function update510() {
$t = ttemplate::i();
$t->footer = str_replace('2011', '2012', $t->footer);
$t->save();

$subscribers =tsubscribers::i();
$subscribers->data['blacklist'] = strtoarray(strtolower(trim($subscribers->data['locklist'])));
unset($subscribers->data['locklist']);
$subscribers->save();

if (dbversion) {
$comusers = tcomusers::i();
$db = $comusers->db;
$items = $db->res2assoc($db->query("select id, email from $comusers->thistable"));
foreach ($items as $item) {
$email = strtolower($item['email']);
if ($email != $item['email']) {
$item['email'] = $email;
$db->updateassoc($item);
}
}
}

}