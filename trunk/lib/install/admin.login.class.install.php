<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminloginInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->lock();
  $urlmap->add('/admin/login/', get_class($self), null, 'normal');
  $urlmap->add('/admin/logout/', get_class($self), 'out', 'normal');
  $urlmap->unlock();
}

function tadminloginUninstall($self) {
  turlmap::unsub($self);
}

?>