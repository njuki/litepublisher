id bigint UNSIGNED NOT NULL auto_increment,
name varchar(64) not null,
owner varchar(64) not null,
class varchar(64) not null,
func varchar(64) not null,

     PRIMARY KEY(id),
key name (name),
key owner (owner),
