<?php

function update542() {
$files = tfiles::i();
$fitems = tfileitems::i();
$db = $fitems->db;
$fi = $fitems->thistable;
$f = $db->files;

$items = $db->res2id($db->query("
select DISTINCT $fi.item from $fi, $f
where $f.id = $fi.item and $f.parent != 0"));
//dumpvar($items);
if (count($items) == 0) return;
$todel  = implode(',', $items);
$posts = $db->res2id($db->query("
select DISTINCT $fi.post from $fi where $fi.item in ($todel)
"));

$fitems->db->delete("item in ($todel)");

foreach ($posts as $id) {
$db->table = $fitems->table;
$items = $db->res2id($db->query("
select item from $fi where post = $id
" ));

$db->table = 'posts';
$db->setvalue($id, 'files', implode(',', $items));
//echo "<br>post = $id,  files = " . implode(',', $items);
}
}