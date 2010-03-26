  `id` int unsigned NOT NULL default '0',
  `class` varchar(32) NOT NULL,
  `parentclass` varchar(32) NOT NULL,
  `childs` text NOT NULL,
  `interfaces` text NOT NULL,
  `dependent` text NOT NULL,
  `methods` text NOT NULL,
  `properties` text NOT NULL,
  `classevents` text NOT NULL,
  `example` longtext NOT NULL,

  KEY `id` (`id`)