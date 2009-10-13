id bigint UNSIGNED NOT NULL auto_increment,
post bigint UNSIGNED NOT NULL default 0,
parent bigint UNSIGNED NOT NULL default 0,
author bigint UNSIGNED NOT NULL default 0,
dbdate datetime NOT NULL default '0000-00-00 00:00:00',
dbmodified datetime NOT NULL default '0000-00-00 00:00:00',
content text not null,
status enum('approved', 'hold', 'spam', 'deleted') default 'approved',
type enum('', 'pingback') default '',

     PRIMARY KEY(id),
key post (post),
key parent (parent),
key dbdate (dbdate),
key status (status)

