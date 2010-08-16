<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminhoverpluginInstall($self) {
  $template = ttemplate::instance();
  $template->lock();
  $template->onadminhead = $self->onadminhead;
  $template->unlock();
}

function tadminhoverpluginUninstall($self) {
  $template = ttemplate::instance();
  $template->unsubscribeclass($self);
}

?>