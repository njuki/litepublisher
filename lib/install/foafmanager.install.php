<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TFoafManagerInstall($self) {
  $actions = TXMLRPCOpenAction ::instance();
  $actions->lock();
  $actions->add('friend.invate', get_class($self), 'Invate');
  $actions->add('friend.reject', get_class($self), 'Reject');
  $actions->add('friend.accept', get_class($self), 'Accept');
  $actions->unlock();
  
  $cron = tcron::instance();
  $cron->add('day', get_class($self), 'CheckFriendship', null);
}

function TFoafManagerUninstall($self) {
  turlmap::unsub($self);
  $actions = TXMLRPCOpenAction ::instance();
  $actions->deleteclass(get_class($self));
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
}

?>