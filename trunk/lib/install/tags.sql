id int UNSIGNED NOT NULL auto_increment,
parent int UNSIGNED NOT NULL default 0,
idurl int UNSIGNED NOT NULL default 0,
itemscount  int UNSIGNED NOT NULL default 0,
icon int UNSIGNED NOT NULL default 0,
title varchar(255) not null,

     PRIMARY KEY(id),
key parent (parent)
