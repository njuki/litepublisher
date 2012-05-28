id int unsigned NOT NULL auto_increment,
  total int UNSIGNED NOT NULL default 0,
  rate tinyint unsigned NOT NULL default '0',
  status enum('opened','closed') default 'opened',
  type enum('star', 'radio','button','link','custom') default 'star',
  title text NOT NULL,
  items text NOT NULL,
  votes text NOT NULL,
  
  PRIMARY KEY  ( id),
  KEY total(total),
  KEY rate (rate)