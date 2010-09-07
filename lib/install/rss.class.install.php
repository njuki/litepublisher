<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $urlmap->add('/rss.xml', get_class($self), 'posts');
  $self->idcomments = $urlmap->add('/comments.xml', get_class($self), 'comments');
  $self->idpostcomments =   $urlmap->add('/comments/', get_class($self), null, 'tree');
  $urlmap->unlock();
  
  litepublisher::$classes->commentmanager->changed = $self->commentschanged;
  $self->save();
  
  $meta = tmetawidget::instance();
  $meta->lock();
  $meta->add('rss', '/rss.xml', tlocal::$data['default']['rss']);
  $meta->add('comments', '/comments.xml', tlocal::$data['default']['rsscomments']);
  $meta->unlock();
}

function trssUninstall($self) {
  turlmap::unsub($self);
  litepublisher::$classes->commentmanager->unsubscribeclass($self);
  $meta = tmetawidget::instance();
  $meta->lock();
  $meta->delete('rss');
  $meta->delete('comments');
  $meta->unlock();
}

?>