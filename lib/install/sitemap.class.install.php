<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsitemapInstall($self) {
  global $options;
  $cron = tcron::instance();
  $cron->add('day', get_class($self),  'Cron', null);
  $urlmap = turlmap::instance();
  $urlmap->add('/sitemap.xml', get_class($self), 'xml');
  $urlmap->add('/sitemap.htm', get_class($self), null);
  
  $robots = trobotstxt ::instance();
  array_splice($robots->items, 1, 0, "Sitemap: $options->url/sitemap.xml");
  $robots->save();
  
  $self->add('/sitemap.htm', 4);
  $self->createfiles();
}

function tsitemapUninstall($self) {
  turlmap::unsub($self);
}

?>