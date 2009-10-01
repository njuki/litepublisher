<?php
function Update274() {
$posts = TPosts::instance();
foreach ($posts->items as $id => $item) {
$post = TPost::Instance($id);
$post->Data['filtered'] = $post->Data['content'];
unset($post->Data['content']);
$post->save();
$post->free();
}
}

?>