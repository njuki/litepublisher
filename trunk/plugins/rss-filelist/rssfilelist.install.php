<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssfilelistInstall($self) {
  $rss = trss::i();
  $rss->beforepost = $self->beforepost;
  
  litepublisher::$urlmap->clearcache();
}

function trssfilelistUninstall($self) {
  $rss = trss::i();
  $rss->unsubscribeclass($self);
  
  litepublisher::$urlmap->clearcache();
}