<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloaditemcounterInstall($self) {
  $cron = tcron::instance();
  $cron->add('hour', get_class($self), 'updatestat');
  
  $urlmap = turlmap::instance();
  $urlmap->add('/downloaditem.htm', get_class($self), 'get');
  
  $robot = trobotstxt::instance();
  $robot->AddDisallow('/downloaditem.htm');
}

function tdownloaditemcounterUninstall($self) {
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
  
  turlmap::unsub($self);
}