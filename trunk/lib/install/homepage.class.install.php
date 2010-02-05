<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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