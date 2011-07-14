  `id` int(10) unsigned NOT NULL auto_increment,
  `avatar` int(10) unsigned NOT NULL default '0',
  `trust` int(10) unsigned NOT NULL default '0',
`name` text NOT NULL,
  `email` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `cookie` char(22) NOT NULL,
  `ip` varchar(15) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `cookie` (`cookie`),
  KEY `email` (`email`),
  KEY `url` (`url`)