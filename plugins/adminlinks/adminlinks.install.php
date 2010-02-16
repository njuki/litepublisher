<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminlinkspluginInstall($self) {
  $template = ttemplate::instance();
  $template->onadminsitebar = $self->onsitebar;
}

function tadminlinkspluginUninstall($self) {
  $template = ttemplate::instance();
  $template->unsubscribeclass($self);
}

?>