  `id` int(10) unsigned NOT NULL auto_increment,
  `idurl` int(10) unsigned NOT NULL default '0',
  `parent` int(10) unsigned NOT NULL default '0',
  `customorder` int(10) unsigned NOT NULL default '0',
  `itemscount` int(10) unsigned NOT NULL default '0',
  `icon` int(10) unsigned NOT NULL default '0',
  `idview` int(10) unsigned NOT NULL default '1',
  `idperm` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)