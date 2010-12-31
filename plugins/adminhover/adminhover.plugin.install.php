<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminhoverpluginInstall($self) {
  $admin = tadminmenus::instance();
  $admin->onbeforemenu = $self->beforemenu;
}

function tadminhoverpluginUninstall($self) {
  $admin = tadminmenus::instance();
  $admin->unsubscribeclass($self);
}

?>