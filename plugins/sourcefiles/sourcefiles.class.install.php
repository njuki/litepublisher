<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsourcefilesInstall($self) {
  if (!dbversion) die("Plugin required data base");
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->table, "
  `id` int unsigned NOT NULL auto_increment,
  `idurl` int unsigned NOT NULL default '0',
  `filename` varchar(128) NOT NULL,
  `dir` varchar(128) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `content` longtext NOT NULL,
  
  PRIMARY KEY  (`id`)
  ");
}

function tsourcefilesUninstall($self) {
  //die("Warning! You can lost all tickets!");
  $manager = tdbmanager ::instance();
  $manager->deletetable($self->table);
  
  Turlmap::unsub($self);
}

?>