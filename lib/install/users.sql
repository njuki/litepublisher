id int UNSIGNED NOT NULL auto_increment,
login varchar(32) not null,
password varchar(64) not null,
name varchar(64) NOT NULL,
email varchar(64) NOT NULL,
url varchar(255) NOT NULL,
cookie varchar(32) NOT NULL,
expired datetime NOT NULL default '2010-01-01 10:01:01',
ip varchar(15) NOT NULL,
gid int UNSIGNED NOT NULL default 0,
trust int UNSIGNED NOT NULL default 0,
avatar int UNSIGNED NOT NULL default 0,


     PRIMARY KEY(id),
key cookie (cookie)