<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcomusersInstall($self) {
  $manager = TDBManager ::instance();
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
  $manager->CreateTable($self->table, file_get_contents($dir .'comments.users.sql'));
  
  $urlmap = turlmap::instance();
  $urlmap->add('/comusers.htm', get_class($self), 'get');
  
  $robots = TRobotstxt ::instance();
  $robots->AddDisallow('/comusers.htm');
}

function tcomusersUninstall($self) {
  turlmap::unsub($self);
}

?>