  `id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL default '2010-01-01 10:01:01',
  `modified` datetime NOT NULL default '2010-01-01 10:01:01',
  `rawcontent` longtext NOT NULL,
  `hash` varchar(22) default NULL,

  PRIMARY KEY  (`id`)