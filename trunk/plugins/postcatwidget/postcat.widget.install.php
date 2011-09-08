<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpostcatwidgetInstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleted = $self->widgetdeleted;
  
  tcategories::instance()->deleted = $self->tagdeleted;
}

function tpostcatwidgetUninstall($self) {
  tcategories::instance()->unsubscribeclass($self);
  $widgets = twidgets::instance();
  $widgets->unsubscribeclass($self);
}