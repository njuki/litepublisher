<?php

/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentformInstall($self) {
  $url= '/send-comment.php';
  
  $urlmap = turlmap::instance();
  $urlmap->Add($url, get_class($self), null);
}

function tcommentformUninstall($self) {
  turlmap::unsub($self);
}

?>