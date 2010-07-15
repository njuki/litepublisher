<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfriendswidgetInstall($self) {
  litepublisher::$urlmap->add($self->redirlink, get_class($self), false, 'get');
}

function tfriendswidgetUninstall($self) {
$widgets = twidgets::instance();
$widgets->deleteclass(get_class($self));
  turlmap::unsub($self);
}

?>