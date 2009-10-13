id bigint UNSIGNED NOT NULL auto_increment,
post bigint UNSIGNED NOT NULL,
page int unsigned not null,
content text not null,

     PRIMARY KEY(id),
key post (post)
