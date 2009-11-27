<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcomusersInstall($self) {
if (dbversion) {
    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'comments.users.sql'));
} else {
}

  $urlmap = turlmap::instance();
  $urlmap->add('/comusers/', get_class($self), 'tree');
  
  $robots = TRobotstxt ::instance();
  $robots->AddDisallow('/comusers/');
}

function tcomusersUninstall($self) {
  tposts::unsub($self);
  turlmap::unsub($self);
}

?>