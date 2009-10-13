id bigint UNSIGNED NOT NULL,
class varchar(64) not null,
rawcontent text not null,

primary key id (id),
key class (class)