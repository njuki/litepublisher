<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
if (!dbversion) die("Plugin can be installed only on database version");
$about = tplugins::localabout(dirname(__file__));
$self->title = $about['title'];
$templates = parse_ini_file(dirname(__file__) . DIRECTORY_SEPARATOR . 'templates.ini',  true);
$self->templateitems = $templates['item'];
$self->templates = $templates['items'];
$self->save();

    $manager = tdbmanager::instance();
    $manager->createtable($self->table,
    "id int UNSIGNED NOT NULL auto_increment,
status enum('opened', 'closed') default  'opened',
type enum('radio', 'button', 'link', 'custom') default 'radio',
hash varchar(32) not null,
title text not null,
items text not null,
votes text not null,

    PRIMARY KEY(id),
key hash(hash)
    ");

    $manager->createtable($self->userstable,
    'id int UNSIGNED NOT NULL auto_increment,
cookie varchar(32) NOT NULL,

    PRIMARY KEY(id),
key cookie(cookie)
');

    $manager->createtable($self->votestable,
'id int UNSIGNED NOT NULL default 0,
user int UNSIGNED NOT NULL default 0,
vote int UNSIGNED NOT NULL default 0,
    PRIMARY KEY(id, user)
');

$cron = tcron::instance();
$cron->addweekly(get_class($self), 'optimize', null);

    $filter = tcontentfilter::instance();
$filter->lock();
  $filter->beforecontent = $self->beforefilter;
$filter->beforefilter = $self->filter;
$filter->unlock();

$xmlrpc = TXMLRPC::instance();
$xmlrpc->lock();
$xmlrpc->add('litepublisher.poll.sendvote', 'sendvote', get_class($self));
$xmlrpc->add('litepublisher.poll.getcookie', 'getcookie', get_class($self));
$xmlrpc->unlock();
}

function tpollsUninstall($self) {
$cron = tcron::instance();
$cron->deleteclass(get_class($self));

  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
$xmlrpc = TXMLRPC::instance();
$xmlrpc->deleteclass(get_class($self));

    $manager = tdbmanager::instance();
    $manager->deletetable($self->table);
    $manager->deletetable($self->userstable);
    $manager->deletetable($self->votestable);
}

?>