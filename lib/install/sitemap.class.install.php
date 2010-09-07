<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsitemapInstall($self) {
  $cron = tcron::instance();
  $cron->add('day', get_class($self),  'Cron', null);
  $urlmap = turlmap::instance();
  $urlmap->add('/sitemap.xml', get_class($self), 'xml');
  $urlmap->add('/sitemap.htm', get_class($self), null);
  
  $robots = trobotstxt ::instance();
  array_splice($robots->items, 1, 0, "Sitemap: " . litepublisher::$options->url . "/sitemap.xml");
  $robots->save();
  
  $self->add('/sitemap.htm', 4);
  $self->createfiles();
  
  $meta = tmetawidget::instance();
  $meta->add('sitemap', '/sitemap.htm', tlocal::$data['default']['sitemap']);
}

function tsitemapUninstall($self) {
  turlmap::unsub($self);
  $meta = tmetawidget::instance();
  $meta->delete('sitemap');
}

?>