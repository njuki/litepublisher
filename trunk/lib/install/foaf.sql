id int UNSIGNED NOT NULL auto_increment,
url varchar(255) not null,
foafurl varchar(255) not null,
nick text NOT NULL,
posted datetime NOT NULL default '2010-01-01 10:01:01',

status enum('published', 'draft', 'future', 'deleted') default 'published',

     PRIMARY KEY(id),
key posted (posted),
key status (status),
key parent (parent)