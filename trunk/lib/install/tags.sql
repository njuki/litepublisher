  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned NOT NULL default '0',
  `idurl` int(10) unsigned NOT NULL default '0',
  `itemscount` int(10) unsigned NOT NULL default '0',
  `icon` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)