<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssMultimediaInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $urlmap->add('/rss/multimedia.xml', get_class($self), '');
  $urlmap->add('/rss/multimedia.xml', get_class($self), '');
  $urlmap->add('/rss/images.xml', get_class($self), 'image');
  $urlmap->add('/rss/audio.xml', get_class($self), 'audio');
  $urlmap->add('/rss/video.xml', get_class($self), 'video');
  $urlmap->unlock();
  
  $files = tfiles::instance();
  $files->changed = $self->fileschanged;
  $self->save();
  
  $meta = tmetawidget::instance();
  $meta->add('media', '/rss/multimedia.xml', tlocal::$data['default']['rssmedia']);
}

function trssMultimediaUninstall($self) {
  turlmap::unsub($self);
  $files = tfiles::instance();
  $files->unsubscribeclass($self);
  
  $meta = tmetawidget::instance();
  $meta->delete('media');
}

?>