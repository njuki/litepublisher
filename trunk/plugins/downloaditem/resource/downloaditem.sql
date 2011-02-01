  `id` int unsigned NOT NULL default '0',
  `type` enum('theme','plugin') default 'theme',
  `votes` int unsigned NOT NULL default '0',
  `poll` int unsigned NOT NULL default '0',
  `version` varchar(5) NOT NULL,
  `downloadurl` varchar(255) NOT NULL,
  `authorurl` varchar(255) NOT NULL,
  `authorname` text NOT NULL,

  PRIMARY KEY  (`id`,`type`),
  KEY `type` (`type`)
