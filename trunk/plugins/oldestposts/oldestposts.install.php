<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function toldestpostsInstall($self) {
  $widgets = twidgets::instance();
  $widgets->addclass($self, 'tpost');
}

?>