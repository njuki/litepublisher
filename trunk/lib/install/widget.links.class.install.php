<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlinkswidgetInstall($self) {
  tlocal::loadlang('admin');
  $lang = &tlocal::$data['installation'];
  $self->add($lang['homeurl'], $lang['homedescription'], $lang['homename']);
  
  $urlmap = turlmap::instance();
  $urlmap->add($self->redirlink, get_class($self), null, 'get');
  
  $robots = trobotstxt ::instance();
  $robots->AddDisallow($self->redirlink);
  $robots->save();
}

function tlinkswidgetUninstall($self) {
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
  turlmap::unsub($self);
}

?>