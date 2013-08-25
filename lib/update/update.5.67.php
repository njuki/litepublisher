<?php

function update567() {
$m = tadminmenus::i();
if ($id = $m->url2id('/admin/comments/authors/')) {
$m->items[$id]['class'] = 'tadmincomusers';
$m->save();

litepublisher::$urlmap->db->setvalue($m->items[$id]['idurl'], 'class', 'tadmincomusers');
}
}