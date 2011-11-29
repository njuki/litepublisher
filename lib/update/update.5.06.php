<?php

function update506() {
litepublisher::$options->comments_invert_order = false;
  litepublisher::$options->hidefilesonpage = false;

$menus = tmenus::i();
foreach ($menus->items as $id => $item) {
$menu = tmenu::i($id);
$menu->data['head'] = '';
$menu->save();
}

foreach (tadminviews::getspecclasses() as $classname) {
$obj = getinstance($classname);
if (!isset($obj->data['head'])) {
$obj->data['head'] = '';
$obj->save();
}
}

if (dbversion) {
$man = tdbmanager::i();
$man->alter('posts', "add `head` text NOT NULL after description");
$man->alter('catscontent', "add `head` text NOT NULL after description");
$man->alter('tagscontent', "add `head` text NOT NULL after description");
$man->alter('userpage', "add `head` text NOT NULL after description");
} else {
add_head(tcategories::i());
add_head(ttags::i());

$posts = tposts::i();
if (count($posts->items) < 100) {
foreach ($posts->items as $id => $item) {
$post = tpost::i($id);
$post->data['head'] = '';
$post->save();
}
}
}
}

function add_head($tags) {
foreach ($tags->items as $id => $item) {
$c = $tags->contents->getitem($id);
if (!isset($c['head'])) {
$c['head'] = '';
$tags->contents->setitem($id, $c);
}
}
}