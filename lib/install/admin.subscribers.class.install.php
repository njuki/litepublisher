<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminsubscribersInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->add('/admin/subscribers/', get_class($self), null, 'normal');
}

function tadminsubscribersUninstall($self) {
  turlmap::unsub($self);
}

?>