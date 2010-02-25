<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpolsInstall($self) {
  if (dbversion) {
    $manager = tdbmanager::instance();
    $manager->createtable($self->table,
    'id int UNSIGNED NOT NULL auto_increment,
    post int UNSIGNED NOT NULL default 0,
    refcount int UNSIGNED NOT NULL default 0,
status enum('opened', 'closed') default  'opened',

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
pol int UNSIGNED NOT NULL default 0,
user int UNSIGNED NOT NULL default 0,
value int UNSIGNED NOT NULL default 0,
    PRIMARY KEY(pol, user)
');

$posts = tposts::instance();
$posts->deleted = $self->postdeleted;
  }
  
  $filter = tcontentfilter::instance();
$filter->lock();
  $filter->beforecontent = $self->createpol;
$filter->beforefilter = $self->filter;
$filter->unlock();
}

function tpolsUninstall($self) {
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