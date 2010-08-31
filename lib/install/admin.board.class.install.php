<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminboardInstall($self) {
  litepublisher::$urlmap->add('/admin/', get_class($self), null, 'normal');
}

function tadminboardUninstall($self) {
  turlmap::unsub($self);
}

?>