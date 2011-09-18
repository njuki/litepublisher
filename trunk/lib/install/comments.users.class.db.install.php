<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcomusersInstall($self) {
  $manager = TDBManager ::i();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.users.sql'));
  
  litepublisher::$urlmap->addget('/comusers.htm', get_class($self));
  
  $robots = TRobotstxt ::i();
  $robots->AddDisallow('/comusers.htm');
}

function tcomusersUninstall($self) {
  turlmap::unsub($self);
}

?>