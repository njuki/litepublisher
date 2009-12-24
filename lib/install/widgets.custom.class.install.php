<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcustomwidgetInstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleted= $self->widgetdeleted;
}

function tcustomwidgetUninstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>