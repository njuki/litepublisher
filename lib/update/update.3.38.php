<?php
function update338() {
litepublisher::$classes->add('tini2array', 'ini2array.class.php');

$posts = tposts::instance();
$posts->data['revision'] = 0;
$posts->save();

if (dbversion) {
$man = tdbmanager ::instance();
$man->alter($posts->table, 'add revision int unsigned NOT NULL default 0');
} else {
foreach ($posts->items as $id => $item) {
$post = tpost::instance($id);
$post->data['revision'] = 0;
$post->save();
$post->free();
}
}

}

?>