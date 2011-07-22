<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcomusersInstall($self) {
  litepublisher::$urlmap->addget('/comusers.htm', get_class($self));  
  $robots = TRobotstxt ::instance();
  $robots->AddDisallow('/comusers.htm');
}

function tcomusersUninstall($self) {
  turlmap::unsub($self);
}

?>