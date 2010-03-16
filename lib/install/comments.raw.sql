id int UNSIGNED NOT NULL,
created datetime NOT NULL default '2010-01-01 10:01:01',
modified datetime NOT NULL default '2010-01-01 10:01:01',
rawcontent longtext not null,
hash char(32) not null,

primary key id (id),
key hash(hash)