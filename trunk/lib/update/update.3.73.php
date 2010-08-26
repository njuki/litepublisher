<?php
function update373() {
unset(litepublisher::$classes->items['tnode']);
litepublisher::$classes->items['toauth']  = array('oauth.class.php', '');
litepublisher::$classes->save();

$menus = tmenus::instance();
if ($idmenu = $menus->url2id('/')) {
$menus->lock();
$menus->remove($idmenu);
while ($idmenu = $menus->url2id('/')) {
$menus->remove($idmenu);
}
$menus->unlock();
}

$home = thomepage::instance();
if (!litepublisher::$urlmap->findurl('/')) {
  $home->idurl = litepublisher::$urlmap->add('/', get_class($home), null);
}
unset($home->data['idmenu']);
$home->data['image'] = '';
  $home->save();

}
?>