id int UNSIGNED NOT NULL auto_increment,
type enum('single', 'hour', 'day', 'week') default 'single',
date datetime NOT NULL default '2010-01-01 10:01:01',
class varchar(64) not null,
func varchar(64) not null,
arg varchar(256)not null,
     PRIMARY KEY(id),
key type (type),
key date(date)