<?php
function update360() {
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