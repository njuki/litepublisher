<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function uloginInstall($self) {
  tusers::i()->deleted = tregserviceuser::i()->delete;
    tdbmanager::i()->createtable('regservices',
    "id int unsigned NOT NULL default 0,
    service enum('$names') default 'google',
    uid varchar(22) NOT NULL default '',
    
    key `id` (`id`),
    KEY (`service`, `uid`)
    ");
  }
  
  litepublisher::$urlmap->addget($self->url, get_class($self));
  tcommentform::i()->oncomuser = $self->oncomuser;
  litepublisher::$urlmap->clearcache();
}

function uloginUninstall($self) {
  tcommentform::i()->unbind($self);
  turlmap::unsub($self);
  tusers::i()->unbind('tregserviceuser');
  tdbmanager::i()->deletetable('regservices');
}