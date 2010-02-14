<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tsamepostsInstall($self) {
if (dbversion) {
    $manager = tdbmanager ::instance();
$manager->createtable($self->table, 
'id int UNSIGNED NOT NULL default 0,
items text NOT NULL,
    PRIMARY KEY(id) ');
}

  $template = ttemplate::instance();
$template->addsitebarclass(litepublisher::$classes->classes['post'], $self->onsitebar);

  $posts= tposts::instance();
  $posts->changed = $self->postschanged;
 }
 
function tsamepostsUninstall($self) {
  $template = ttemplate::instance();
$template->deletesitebarclass(litepublisher::$classes->classes['post'], $self);

  tposts::unsub($self);
if (dbversion) {
    $manager = tdbmanager ::instance();
$manager->deletetable($self->table);
} else {
$posts = tposts::instance();
$dir = litepublisher::$paths->data . 'posts' . DIRECTORY_SEPARATOR;
foreach ($posts->items as $id => $item) {
@unlink($dir . $id .DIRECTORY_SEPARATOR . 'same.php');
}
}
}

?>