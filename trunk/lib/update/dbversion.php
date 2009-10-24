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

function migratesubscribe() {
$users = TComUsers::instance();
$users->basename = 'commentusers';
$users->load();
$subscribers = tsubscribers::instance();
$subscribers->lock();
foreach ($users->items as $uid => $item) {
foreach ($item['subscribe'] as $pid) $subscribers->add($pid, $uid);
unset($users->items[$uid]['subscribe']);
}
$subscribers->unlock();
$users->basename = 'comusers';
$users->save();
//unlink($paths['data'] . 'commentusers.php');
}


function autoid($class) {
$obj = getinstance($class);
$classdata['autoid'] = $obj->data['lastid'];
unset($obj->data['lastid']);
$obj->save();
}

function updateautoid() {
autoid('TPosts');
autoid('TPlugins');
autoid('TMenu');
autoid('TLinksWidget');
autoid('TFiles');
autoid('TCron');
autoid('TCommentUsers');
autoid('TTags');
autoid('TCategories');
autoid('TCommentManager'); 
autoid('TFoaf');
autoid('TUrlmap');
}
function newupdate() {
global $paths, $options, $classes, $urlmap;
$urlmap->lock();
$options->lock();
updateautoid();
unset($classes->items['ITemplate']);
$classes->interfaces['ITemplate'] = array('interfaces.php', '');
unset($classes->items['TSubscribe']);
$classes->items[tsSubscribers'] = array('commentsubscribe.php', ''); 
$classes->items['tcomusers'] => $classes->items['TCommentUsers'];
unset($classes->items['TCommentUsers']);
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