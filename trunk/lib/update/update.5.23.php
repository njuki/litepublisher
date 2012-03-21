<?php

function update523() {
$u = tusers::i();
$p = tuserpages::i();
if (dbversion) {
$man = tdbmanager::i();
$man->alter($u->table, "drop index login"); 
$man->alter($u->table, "drop login"); 
$man->alter($u->table, "add   `email` varchar(64) NOT NULL after id");
$man->alter($u->table, "add   index `email` (`email`)");
$man->alter($u->table, "add   `name` text not null");
$man->alter($u->table, "add   `website` varchar(255) NOT NULL");

$p->loadall();
foreach ($p->items as $id => $item) {
$u->db->updateassoc(array(
'id' => $id,
'email' => $item['email'],
'name' => $item['name'],
'website' => $item['website']
));
}
$man->alter($p->table, "drop email"); 
$man->alter($p->table, "drop name"); 
$man->alter($p->table, "drop website"); 
} else {
foreach ($u->items as $id => $item) {
foreach (array('email', 'name', 'website') as $name) {
$u->items[$id][$name] =  $p->items[$id][$name];
unset($p->items[$id][$name]);
}
}
$u->save();
$p->save();
}
}