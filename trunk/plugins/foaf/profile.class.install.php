<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprofileInstall($self) {
  litepublisher::$Urlmap->add($self->url, get_class($self), null);
  
  $sitemap = tsitemap::instance();
  $sitemap->add($self->url, 7);
  
  $template = ttemplate::instance();
  $template->addtohead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');

$merger = tlocalmerger::instance();
$merger->lock();
$merger->add('default', sprintf('plugins/%s/resource/%s.ini', basename(dirname(__file__)), litepublisher::$options->language));
$merger->addhtml(sprintf('plugins/%s/resource/html.ini', basename(dirname(__file__)));
$merger->unlock();
}

function tprofileUninstall($self) {
  turlmap::unsub($self);
  
  $sitemap = tsitemap::instance();
  $sitemap->delete('/profile.htm');
  
  $template = ttemplate::instance();
  $template->deletefromhead('	<link rel="author profile" title="Profile" href="$site.url/profile.htm" />');

$merger = tlocalmerger::instance();
$merger->lock();
$merger->deletefile('default', sprintf('plugins/%s/resource/%s.ini', basename(dirname(__file__)), litepublisher::$options->language));
$merger->deletehtml(sprintf('plugins/%s/resource/html.ini', basename(dirname(__file__)));
$merger->unlock();
}

?>