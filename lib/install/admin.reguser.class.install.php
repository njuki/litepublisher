<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminreguserInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->add('/admin/reguser/', get_class($self), null, 'normal');
}

function tadminreguserUninstall($self) {
  turlmap::unsub($self);
}

?>