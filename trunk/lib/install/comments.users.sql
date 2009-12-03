id int UNSIGNED NOT NULL auto_increment,
avatar int UNSIGNED NOT NULL default 0,
trust int UNSIGNED NOT NULL default 0,
name varchar(64) NOT NULL,
email varchar(64) NOT NULL,
url varchar(255) NOT NULL,
cookie varchar(32) NOT NULL,
ip varchar(15) NOT NULL,

     PRIMARY KEY(id),
key cookie (cookie),
key email(email),
key url (url)