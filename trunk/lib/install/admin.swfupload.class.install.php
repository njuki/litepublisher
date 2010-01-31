<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminswfuploadInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->add('/admin/swfupload/', get_class($self), null, 'normal');
}

function tadminswfuploadUninstall($self) {
turlmap::unsub($self);
}

?>