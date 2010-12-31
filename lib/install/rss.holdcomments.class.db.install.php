<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssholdcommentsInstall($self) {
  $self->idurl = litepublisher::$urlmap->add($self->url, get_class($self), null, 'get');
  $self->save();
}

function trssholdcommentsUninstall($self) {
  turlmap::unsub($self);
  litepublisher::$classes->commentmanager->unsubscribeclass($self);
}

?>