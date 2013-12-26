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

}