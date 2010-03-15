<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
function trobotstxtInstall($self) {
  $self->lock();
  $urlmap = turlmap::instance();
  $self->idurl = $urlmap->add('/robots.txt', get_class($self), null);
  
  $self->add("#" . litepublisher::$options->url . "/");
  $self->add('User-agent: *');
  $self->AddDisallow('/rss.xml');
  $self->AddDisallow('/comments.xml');
  $self->AddDisallow('/comments/');
  $self->AddDisallow('/admin/');
  $self->unlock();
}

function trobotstxtUninstall($self) {
  turlmap::unsub($self);
}

?>