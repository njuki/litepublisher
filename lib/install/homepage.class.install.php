<?php

function THomepageInstall(&$self) {
  global $options;
  $options->home = '/';
  $urlmap = turlmap::instance();
  $self->idurl = $urlmap->add($options->home, get_class($self), null);
$self->save();
}

function THomepageUninstall(&$self) {
  turlmap::unsub($self);
}

?>