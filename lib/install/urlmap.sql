id bigint UNSIGNED NOT NULL auto_increment,
url varchar(255) not null,
class varchar(64) not null,
arg varchar(64) not null,
type enum('normal', 'get', 'tree') default 'normal',

     PRIMARY KEY(id), 
key url (url),
key class (class)
