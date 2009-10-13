id bigint UNSIGNED NOT NULL auto_increment,
post bigint UNSIGNED NOT NULL ,
user bigint UNSIGNED NOT NULL ,

     PRIMARY KEY(id),
key post (post),
key user (user)
