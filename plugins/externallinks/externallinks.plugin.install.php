<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function texternallinksInstall($self) {
  if (dbversion) {
    $manager = tdbmanager::i();
    $manager->createtable($self->table,
    'id int UNSIGNED NOT NULL auto_increment,
    clicked int UNSIGNED NOT NULL default 0,
    url varchar(255)not null,
    PRIMARY KEY(id),
    key url (url)
    ');
  } else {
  }
  
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->afterfilter = $self->filter;
  $filter->onaftercomment = $self->filter;
  $filter->unlock();
  
  $cron = tcron::i();
  $cron->add('hour', get_class($self), 'updatestat');
  
  litepublisher::$urlmap->addget('/externallink.htm', get_class($self));
  
  $robot = trobotstxt::i();
  $robot->AddDisallow('/externallink.htm');
}

function texternallinksUninstall($self) {
  $filter = tcontentfilter::i();
  $filter->unsubscribeclass($self);
  
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
  
  turlmap::unsub($self);
  
  if (dbversion) {
    $manager = tdbmanager::i();
    $manager->deletetable($self->table);
    
    $posts = tposts::i();
    $posts->addrevision();
  }
}