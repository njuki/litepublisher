id bigint UNSIGNED NOT NULL auto_increment,
idurl bigint UNSIGNED NOT NULL default 0,
parent bigint UNSIGNED NOT NULL default 0,
author bigint UNSIGNED NOT NULL default 0,

created datetime NOT NULL default '0000-00-00 00:00:00',
dbmodified datetime NOT NULL default '0000-00-00 00:00:00',

title text NOT NULL,
filtered longtext not null,
excerpt text not null,
rss text not null,
description text not null,
moretitle varchar(255) not null,

categories varchar(1024) not null,
tags varchar(1024) not null,

password varchar(64) not null,
template varchar(64) not null,
theme varchar(64) not null,

status enum('published', 'draft', 'future') default 'published',

commentsenabled boolean default true,
pingenabled boolean default true,
rssenabled boolean default true,

commentscount int unsigned not null default 0,
pagescount int unsigned not null default 0,

     PRIMARY KEY(id),
key parent (parent),
key created(created),
key status (status)