<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
  
  $widgets = twidgets::instance();
  $widgets->addclass($self, 'tpost');
  
  $posts = tposts::instance();
  $posts->changed = $self->postschanged;
}

function tsamepostsUninstall($self) {
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