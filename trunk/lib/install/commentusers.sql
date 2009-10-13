id bigint UNSIGNED NOT NULL auto_increment,
name varchar(64) NOT NULL,
email varchar(64) NOT NULL,
url varchar(255) NOT NULL,
cookie varchar(32) NOT NULL,
ip varchar(15) NOT NULL,

     PRIMARY KEY(id),
key cookie (cookie),
key uid (email, url)