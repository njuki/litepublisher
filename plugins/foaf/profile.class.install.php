<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprofileInstall($self) {
  $Urlmap = turlmap::instance();
  $Urlmap->add($self->url, get_class($self), null);
  
  $sitemap = tsitemap::instance();
  $sitemap->add($self->url, 7);
}

function tprofileUninstall($self) {
  turlmap::unsub($self);
  
  $sitemap = tsitemap::instance();
  $sitemap->delete('/profile.htm');
}

?>