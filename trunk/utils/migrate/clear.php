<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');

function cleartags($tags) {
$tags->lock();
$tags->loadall();
foreach ($tags->items as $id => $item) {
$tags->delete($id);
}
$tags->unlock();
}

$posts = tposts::instance();
$posts->lock();
if (dbversion) {
$items = $posts->select(litepublisher::$db->prefix . 'posts.id > 0', '');
var_dump($items);
foreach ($items as $id) {
$posts->delete($id);
}
$posts->deletedeleted();
} else {
foreach ($posts->items as $id => $item) {
$posts->delete($id);
}
}
$posts->unlock();
cleartags(tcategories::instance());
cleartags(ttags::instance());

$man = tdbmanager::instance();
echo  $man->performance();
?>