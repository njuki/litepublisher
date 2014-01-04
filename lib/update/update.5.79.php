<?php
function update579() {
$g = tusergroups::i();
if ($id = $g->getidgroup('commentator')) {
$g->items[$id]['home'] = '/admin/comments/';
if (in_array($id, $g->defaults)) {
array_delete_value($g->defaults, $id);
$g->defaults[] = $id;
}
$g->save();
}

$m = tadminmenus::i();
$m->lock();
$pid = $m->url2id('/admin/posts/');
    $id = $m->createitem($pid, 'addcat', 'editor', 'tadmintags');
$m->items[$id]['order'] = $m->url2id('/admin/posts/categories/');
    $id = $m->createitem($posts, 'addtag', 'editor', 'tadmintags');
$m->items[$id]['order'] = $m->url2id('/admin/posts/tags/');

$m->sort();
$m->unlock();
}