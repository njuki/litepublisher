  `id` int unsigned NOT NULL auto_increment,
  `email` varchar(64) NOT NULL,
  `password` varchar(22) NOT NULL,
  `cookie` char(22) NOT NULL,
  `expired` datetime NOT NULL default '2010-01-01 10:01:01',
  `idgroups` text NOT NULL,
  `trust` int unsigned NOT NULL default '0',
  `status` enum('wait', 'approved','hold','comuser') default 'wait',
  `name` text not null,
  `website` varchar(255) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `email` (`email`),
  KEY `cookie` (`cookie`)
