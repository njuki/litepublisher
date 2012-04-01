<?php

function update526() {
  litepublisher::$options->comments_status = 'guest';

if (dbversion) {
$man = tdbmanager::i();
//$man->alter($table, "add `includeparents` boolean default " . ($tags->includeparents ? 'true' : 'false'));
$man->delete_enum('users', 'status', 'lock');
$man->addenum('users', 'status', 'notconfirmed');

$man->alter('posts', "add `comments_status` enum('closed','reg','guest','email') default 'email'");
$db = litepublisher::$db;
$db->table = 'posts';
$db->update("comments_status = 'closed'", "commentsenabled = 0");
$man->alter('posts', "drop commentsenabled");
}
}