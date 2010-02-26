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
status enum('opened', 'closed') default  'opened',
sign varchar(32) not null,
title text not null,
items text not null,
votes text not null,

    PRIMARY KEY(id),
key sign(sign)
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

$cron = tcron::instance();
$cron->addweekly(get_class($self), 'optimize', null);
  }
  
  $filter = tcontentfilter::instance();
$filter->lock();
  $filter->beforecontent = $self->beforefilter;
$filter->beforefilter = $self->filter;
$filter->unlock();
$xmlrpc = TXMLRPC::instance();
$xmlrpc->lock();
$xmlrpc->add('litepublisher.poll.sendvote', get_class($self), 'sendvote');
$xmlrpc->add('litepublisher.poll.getcookie', get_class($self), 'getcookie');
$xmlrpc->unlock();
}

function tpollsUninstall($self) {
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
$xmlrpc = TXMLRPC::instance();
$xmlrpc->deleteclass(get_class($self));

  if ($self->dbversion) {
    $manager = tdbmanager::instance();
    $manager->deletetable($self->table);
    $manager->deletetable($self->userstable);
    $manager->deletetable($self->resulttable);
  }
}

?>