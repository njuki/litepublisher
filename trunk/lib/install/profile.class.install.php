<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tprofileInstall($self) {
  $Urlmap = turlmap::instance();
  $Urlmap->add('/profile/', get_class($self), null);
}

function tprofileUninstall($self) {
  tulmap::unsub($self);
}

?>