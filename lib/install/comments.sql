id int UNSIGNED NOT NULL auto_increment,
post int UNSIGNED NOT NULL default 0,
author int UNSIGNED NOT NULL default 0,
parent int UNSIGNED NOT NULL default 0,
posted datetime NOT NULL default '0000-00-00 00:00:00',
status enum('approved', 'hold', 'spam', 'deleted') default 'approved',
pingback boolean default false,
content text not null,

     PRIMARY KEY(id),
key post (post),
key author (author),
key posted(posted),
key status (status, pingback)

