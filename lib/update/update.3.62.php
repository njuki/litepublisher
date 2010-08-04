<?php
function update362() {
$home = thomepage::instance();
if (!isset($home->data['idmenu'])) {
      $menus = tmenus::instance();
      $home->data['id'] =$menus->class2id(get_class($home));
$home->save();
}

if (dbversion) {
$admin = tadminmenus::instance();
if ($id = $admin->url2id('/admin/comments/holdrss/')) {
tlocal::loadlang('admin');
$admin->items[$id]['title'] = tlocal::$data['names']['holdrss'];
$admin->save();
ttheme::clearcache();
}
}

$redir = tredirector::instance();
$redir->lock();
$redir->add('/rss', '/rss.xml');
$redir->unlock();
  }

?>