  `id` char(22) NOT NULL,
  `posted` datetime NOT NULL,
  `vals` text NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `posted` (`posted`)