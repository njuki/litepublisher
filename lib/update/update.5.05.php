<?php

function refilter_comments() {
$filter = tcontentfilter::i();
$from = 0;
while ($a = $db->res2assoc($db->query("select id, rawcontent from $db->rawcomments where id > $from limit 600"))) {
$db->table = 'comments';
foreach ($a as $item) {
$s = $filter->filtercomment($item['rawcontent']);
$db->setvalue($item['id'], 'content', $s);
$from = max($from, $item['id']);
}
unset($a);
}
}

function update505() {
//refilter comments
if (dbversion) refilter_comments();
}