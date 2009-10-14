id bigint UNSIGNED NOT NULL auto_increment,
post bigint UNSIGNED NOT NULL default 0,
tag bigint UNSIGNED NOT NULL default 0,

     PRIMARY KEY(id),
key post(post),
key tag(tag)
