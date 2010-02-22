id int UNSIGNED NOT NULL auto_increment,
url varchar(255) not null,
foafurl varchar(255) not null,
nick text NOT NULL,
added datetime NOT NULL default '2010-01-01 10:01:01',

status enum('approved', 'hold', 'future', 'deleted') default 'hold',

     PRIMARY KEY(id),
key url(url),
key status (status)
