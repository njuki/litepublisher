  `id` int unsigned NOT NULL auto_increment,
  `login` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `cookie` varchar(32) NOT NULL,
  `expired` datetime NOT NULL default '2010-01-01 10:01:01',
  `registered` datetime NOT NULL default '2010-01-01 10:01:01',
  `gid` int(10) unsigned NOT NULL default '0',
  `trust` int(10) unsigned NOT NULL default '0',
  `status` enum('approved','hold','lock','wait') default 'wait',
  `name` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `avatar` int(10) unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  KEY `login` (`login`),
  KEY `cookie` (`cookie`),
  KEY `status` (`status`)