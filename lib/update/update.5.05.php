<?php

function refilter_comments() {
$db = litepublisher::$db;
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
litepublisher::$site->jquery_version = '1.7.1';

$backuper = tbackuper::i();
$backuper->filertype = 'ftp';
$backuper->save();

//refilter comments
if (dbversion) refilter_comments();

if (litepublisher::$classes->exists('twikiwords')) {
tposts::i()->delete_event_class('added', 'twikiwords');
}
}