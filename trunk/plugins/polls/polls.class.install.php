<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
  if (dbversion) {
    $manager = tdbmanager::instance();
    $manager->createtable($self->table,
    'id int UNSIGNED NOT NULL auto_increment,
    post int UNSIGNED NOT NULL default 0,
status enum('opened', 'closed') default  'opened',
votes text not null,

    PRIMARY KEY(id),
key post (post)
    ');

    $manager->createtable($self->userstable,
    'id int UNSIGNED NOT NULL auto_increment,
cookie varchar(32) NOT NULL,

    PRIMARY KEY(id),
key cookie(cookie)
');

    $manager->createtable($self->resulttable,
poll int UNSIGNED NOT NULL default 0,
user int UNSIGNED NOT NULL default 0,
vote int UNSIGNED NOT NULL default 0,
    PRIMARY KEY(poll, user)
');

$posts = tposts::instance();
$posts->deleted = $self->postdeleted;
  }
  
  $filter = tcontentfilter::instance();
$filter->lock();
  $filter->beforecontent = $self->beforefilter;
$filter->beforefilter = $self->filter;
$filter->unlock();
}

function tpollsUninstall($self) {
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
  if ($self->dbversion) {
    $manager = tdbmanager::instance();
    $manager->deletetable($self->table);
    $manager->deletetable($self->userstable);
    $manager->deletetable($self->resulttable);
  }
}

?>