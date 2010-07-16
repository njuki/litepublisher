<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmenuwidgetInstall($self) {
  $widgets = twidgets::instance();
$widgets->addclass($self, 'tmenu');
}

function tmenuwidgetUninstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>