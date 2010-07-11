  `id` int(10) unsigned NOT NULL auto_increment,
  `class` varchar(64) NOT NULL,
  `pageclass` varchar(64) NOT NULL,
  `type` enum('echo','include','nocache') default 'echo',

  PRIMARY KEY  (`id`),
  KEY `class` (`class`)