  `id` int unsigned NOT NULL auto_increment,
  `login` varchar(32) NOT NULL,
  `password` varchar(22) NOT NULL,
  `cookie` char(22) NOT NULL,
  `expired` datetime NOT NULL default '2010-01-01 10:01:01',
  `gid` int unsigned NOT NULL default '0',
  `trust` int unsigned NOT NULL default '0',
  `status` enum('approved','hold','lock','wait') default 'wait',

  PRIMARY KEY  (`id`),
  KEY `login` (`login`),
  KEY `cookie` (`cookie`)
