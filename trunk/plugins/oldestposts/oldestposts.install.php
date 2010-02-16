<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function toldestpostsInstall($self) {
  $template = ttemplate::instance();
  $template->addsitebarclass(litepublisher::$classes->classes['post'], $self->onsitebar);
}

function toldestpostsUninstall($self) {
  $template = ttemplate::instance();
  $template->deletesitebarclass(litepublisher::$classes->classes['post'], $self);
}

?>