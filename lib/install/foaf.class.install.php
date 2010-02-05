<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafInstall($self) {
  global $options;
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $urlmap->add('/foaf.xml', get_class($self), 'xml');
  $urlmap->add($self->redirlink, get_class($self), 'redir', 'get');
  $urlmap->unlock();
  
  $robots = trobotstxt ::instance();
  $robots->AddDisallow($self->redirlink);
  $robots->save();
}

function tfoafUninstall($self) {
  turlmap::unsub($self);
  
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>