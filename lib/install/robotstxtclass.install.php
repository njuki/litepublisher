<?php

function TRobotstxtInstall(&$self) {
  global $options;
  $self->lock();
  $urlmap = turlmap::instance();
$self->idurl = $Urlmap->add('/robots.txt', get_class($self), null);
  
  $self->add("#$Options->url$Options->home");
  $self->add('User-agent: *');
  $self->AddDisallow('/rss/');
  $self->AddDisallow('/comments/');
  $self->AddDisallow('/admin/');
  $self->AddDisallow('/pda/');
  $self->unlock();
}

function TRobotstxtUninstall(&$self) {
  turlmap::unsub($self);
}

?>