<?php

function updatetags($class) {
global $urlmap;
$tags = getinstance($class);
unset($urlmap->tree[$tags->basename]);
foreach $tags->items as $id => $item) {
$item['title'] $item['name'];
unset($item['name']);
$item['idurl'] = $urlmap->add($item['url'], get_class($tags), $id);
$item['keywords'] = '';
$item['description'] = '';
$tags->items[$id] = $item;
}
$tags->save();
}

function Update280() {
global $paths, $options, $classes, $urlmap;
$urlmap->lock();
$options->lock();
$classes->items['ITemplate'] = array('interfaces.php', '');
$classes->items['TXMLRPCAbstract'] = array('xmlrpc-abstractclass.php', '');
$classes->save();

updatetags('tcategories');
updatetags('ttags');

$posts = tposts::instance();
foreach ($posts->events as $name => $events) {
$posts->events[strtolower($name)] = $events;
}

foreach ($posts->items as $id => $item) {
$post = tpost::instance($id);
$post->idurl = $urlmap->items[$post->url]['id'];
$post->data['pagescount'] = isset($post->data['pages']) ? count($post->pages) : 0;
$post->data['posted'] = $post->data['date'];
unset($post->data['date']);
unset($post->data['theme']);
$post->data['subtheme'] = '';
$post->data['icon'] = 0;
$post->save();
$post->free();
}
$posts->save();

$options->recentcount= $post->recentcount;
$options->version = '2.77';
$options->unlock();

foreach ($urlmap->items as $url => $item) {
$item['type'] = 'normal';
$item['pages'] = 1;
$urlmap->items[$url] = $item;
}

foreach ($urlmap->data['get'] as $url => $item) {
$item['type'] = 'get';
$item['pages'] = 1;
$urlmap->items[$url] = $item;
}

foreach ($urlmap->data['tree']as $url => $item) {
$item['type'] = 'tree';
$item['pages'] = 1;
unset($item['final']);
$url = trim($url, '/');
$url = "/$url/";
if (isset($item['items']) {
foreach ($item['items'] as $suburl => $subitem) {
$subitem['type'] = 'tree';
$subitem['pages'] = 1;
unset($subitem['final']);
$suburl = trim($suburl, '/');
$suburl =$url . $suburl . '/';
$urlmap->items[$suburl] = $subitem;
}
unset($item['items']);
}
$urlmap->items[$url] = $item;
}

unset($urlmap->data['get']);
unset($urlmap->data['tree']);
$urlmap->save();

}
?>