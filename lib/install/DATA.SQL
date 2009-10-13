id bigint UNSIGNED NOT NULL auto_increment,
basename varchar(64) not null,
data text not null,

     PRIMARY KEY(id),
key basename (basename)
