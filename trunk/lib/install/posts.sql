id int UNSIGNED NOT NULL auto_increment,
idurl int UNSIGNED NOT NULL default 0,
icon int UNSIGNED NOT NULL default 0,
parent int UNSIGNED NOT NULL default 0,
author int UNSIGNED NOT NULL default 0,
posted datetime NOT NULL default '2010-01-01 10:01:01',
title text NOT NULL,
title2 text NOT NULL,
filtered longtext not null,
excerpt text not null,
rss text not null,
description text not null,
moretitle varchar(255) not null,

categories text not null,
tags text not null,

password varchar(64) not null,
template varchar(64) not null,
subtheme varchar(64) not null,

status enum('published', 'draft', 'future', 'deleted') default 'published',

commentsenabled boolean default true,
pingenabled boolean default true,
rssenabled boolean default true,

commentscount int unsigned not null default 0,
pagescount int unsigned not null default 0,

     PRIMARY KEY(id),
key posted (posted),
key status (status)
key parent (parent),