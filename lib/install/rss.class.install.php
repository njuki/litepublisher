<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssInstall($self) {
  global $classes;
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $urlmap->add('/rss.xml', get_class($self), 'posts');
  $urlmap->add('/rss/', get_class($self), 'post', 'tree');
  $self->idurlcomments = $urlmap->add('/comments.xml', get_class($self), 'comments');
  $urlmap->unlock();
  
  $classes->commentmanager->changed = $self->commentschanged;
    $self->save();
}

function trssUninstall($self) {
  global $classes;
  turlmap::unsub($self);
  $classes->commentmanager->unsubscribeclass($self);
}

?>