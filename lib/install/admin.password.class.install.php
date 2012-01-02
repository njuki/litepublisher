<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminpasswordInstall($self) {
  $urlmap = turlmap::i();
  $urlmap->add('/admin/password/', get_class($self), null, 'normal');
}

function tadminpasswordUninstall($self) {
  turlmap::unsub($self);
}

?>