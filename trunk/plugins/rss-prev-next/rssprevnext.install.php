<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TRSSPrevNextInstall($self) {
  $rss = trss::instance();
  $rss->beforepost = $self->beforepost;
  
  $urlmap = turlmap::instance();
  $urlmap->clearcache();
}

function TRSSPrevNextUninstall($self) {
  $rss = trss::instance();
  $rss->unsubscribeclass($self);
  
  $urlmap = turlmap::instance();
  $urlmap->clearcache();
}

?>