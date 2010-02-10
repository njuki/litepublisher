<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function THomepageInstall(&$self) {
  litepublisher::$options->home = '/';
  $urlmap = turlmap::instance();
  $self->idurl = $urlmap->add(litepublisher::$options->home, get_class($self), null);
  $self->save();
}

function THomepageUninstall(&$self) {
  turlmap::unsub($self);
}

?>