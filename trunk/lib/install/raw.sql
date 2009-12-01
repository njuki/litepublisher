id int UNSIGNED NOT NULL,
created datetime NOT NULL default '2010-01-01 10:01:01',
modified datetime NOT NULL default '2010-01-01 10:01:01',
rawcontent longtext not null,
hash varchar(32),

primary key id (id)
