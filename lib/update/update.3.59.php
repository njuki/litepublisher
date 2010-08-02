<?php
function update359() {
$notfound = tnotfound404::instance();
if (!isset($notfound->data['notify'])) {
$notfound->data['notify'] = true;
$notfound->save();
}

if (dbversion) {
litepublisher::$classes->add('tadmincommentsrss', 'admin.comments.hold.class.db.php');
$admin = tadminmenus::instance();
$idcomments = $admin->url2id('/admin/comments/');
$admin->createitem($idcomments, 'holdrss', 'moderator', 'tadmincommentsrss');
}


$redir = tredirector::instance();
$redir->add('/rss/', '/rss.xml');
  }

?>