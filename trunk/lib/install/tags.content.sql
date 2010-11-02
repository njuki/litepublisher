  `id` int(10) unsigned NOT NULL,
  `view` int unsigned NOT NULL default '1',
  `content` text NOT NULL,
  `rawcontent` text NOT NULL,
  `description` text NOT NULL,
  `keywords` varchar(255) NOT NULL,

  PRIMARY KEY  (`id`)