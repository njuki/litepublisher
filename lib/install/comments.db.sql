id int UNSIGNED NOT NULL auto_increment,
post int UNSIGNED NOT NULL default 0,
author int UNSIGNED NOT NULL default 0,
parent int UNSIGNED NOT NULL default 0,
posted datetime NOT NULL default '2010-01-01 10:01:01',
status enum('approved', 'hold', 'spam', 'deleted') default 'approved',
ip varchar(15) not null,
content text not null,

     PRIMARY KEY(id),
key post (post),
key author (author),
key posted(posted),
key status (status, pingback)
