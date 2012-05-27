id int unsigned NOT NULL auto_increment,
  rate tinyint unsigned NOT NULL default '0',
  status enum('opened','closed') default 'opened',
  type enum('star', 'radio','button','link','custom') default 'star',
  hash char(22) NOT NULL,
  title text NOT NULL,
  items text NOT NULL,
  votes text NOT NULL,
  
  PRIMARY KEY  ( id),
  KEY rate (rate),
  KEY hash (hash)