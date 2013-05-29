<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function uloginInstall($self) {
    tdbmanager::i()->createtable($self->table, file_get_contents(dirname(__file__) . '/ulogin.sql'));
  tusers::i()->deleted = $self->delete;
  tcommentform::i()->oncomuser = $self->oncomuser;

  litepublisher::$urlmap->addget($self->url, get_class($self));
  litepublisher::$urlmap->clearcache();
}

function uloginUninstall($self) {
  tcommentform::i()->unbind($self);
  tusers::i()->unbind('tregserviceuser');
  turlmap::unsub($self);
  tdbmanager::i()->deletetable('$self->table);
}