<?php
function update359() {
$notfound = tnotfound404::instance();
if (!isset($notfound->data['notify'])) {
$notfound->data['notify'] = true;
$notfound->save();
}

if (dbversion) {
litepublisher::$classes->add('trssholdcomments', 'rss.holdcomments.class.db.php');
tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$idcomments = $admin->url2id('/admin/comments/');
$admin->createitem($idcomments, 'holdrss', 'moderator', 'tadminmoderator');
}

$redir = tredirector::instance();
$redir->lock();
$redir->add('/rss/', '/rss.xml');
$redir->add('/contact.php', '/kontakty.htm');
$redir->add('/feed/', '/rss.xml');
$redir->unlock();
  }

?>