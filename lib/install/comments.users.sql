id int UNSIGNED NOT NULL auto_increment,
name varchar(64) NOT NULL,
email varchar(64) NOT NULL,
url varchar(255) NOT NULL,
avatar int UNSIGNED NOT NULL default 0,
trust int UNSIGNED NOT NULL default 0,
cookie varchar(32) NOT NULL,
ip varchar(15) NOT NULL,

     PRIMARY KEY(id),
key cookie (cookie),
key email(email),
key url (url)