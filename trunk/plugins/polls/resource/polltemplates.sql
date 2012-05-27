id int unsigned NOT NULL auto_increment,
  type enum('star', 'radio','button','link','custom') default 'star',
  title text NOT NULL,
  items text NOT NULL,

  PRIMARY KEY  ( id)