id int UNSIGNED NOT NULL auto_increment,
post int UNSIGNED NOT NULL default 0,
posted datetime NOT NULL default '2010-01-01 10:01:01',
status enum('approved', 'hold', 'spam', 'deleted') default 'hold',
title varchar(64) NOT NULL,
url varchar(255) NOT NULL,
ip varchar(15) not null,

     PRIMARY KEY(id),
key post (post),
key posted(posted),
key status (status)
