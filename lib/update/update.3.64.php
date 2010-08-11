<?php
function update364() {
      //litepublisher::$options->setcookie(litepublisher::$options->cookie);
    litepublisher::$options->data['cookie'] = md5((string) litepublisher::$options->cookie . litepublisher::$secret);
    litepublisher::$options->save();

$users = tusers::instance();
if ($users->count > 0) {
if (dbversion) {
$users->loadall();
$db = $users->db;
foreach ($users->items as $id => $item) {
$db->setvalue($id, 'cookie',  md5($item['cookie'] . litepublisher::$secret));
}
} else {
foreach ($users->items as $id => $item) {
$users->items[$id]['cookie'] = md5($item['cookie'], litepublisher::$secret);
}
$users->save();
}
}

}
?>