  `id` int(10) unsigned NOT NULL default '0',
  `class` varchar(32) NOT NULL,
  `parentclass` int(10) unsigned NOT NULL default '0',
  `childclasses` text NOT NULL,
  `interfaces` text NOT NULL,
  `methods` text NOT NULL,
  `properties` text NOT NULL,
  `eventsdoc` text NOT NULL,
  `example` longtext NOT NULL,

  PRIMARY KEY  (`id`,`type`),
  KEY `parentclass` (`parentclass`)