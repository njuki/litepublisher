<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprofileInstall($self) {
  $Urlmap = turlmap::instance();
  $Urlmap->add('/profile.htm', get_class($self), null);
  
  $sitemap = tsitemap::instance();
  $sitemap->add('/profile.htm', 7);
}

function tprofileUninstall($self) {
  tulmap::unsub($self);
  
  $sitemap = tsitemap::instance();
  $sitemap->delete('/profile.htm');
}

?>