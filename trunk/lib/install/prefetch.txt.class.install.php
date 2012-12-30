<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
function tprefetchtxtInstall($self) {
  $self->lock();
  $urlmap = turlmap::i();
  $self->idurl = $urlmap->add('/prefetch.txt', get_class($self), null);
  
  //$self->add("#" . litepublisher::$site->url . "/");
  $self->add('$template.jsmerger_default');
  $self->add('$template.cssmerger_default');
  $self->unlock();
}

function tprefetchtxtUninstall($self) {
  turlmap::unsub($self);
}