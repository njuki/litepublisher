<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function toldestpostsInstall($self) {
global $classes;
  $template = ttemplate::instance();
$template->addsitebarclass($classes->classes['post'], $self->onsitebar);

 }
 
function toldestpostsUninstall($self) {
global $classes;
  $template = ttemplate::instance();
$template->deletesitebarclass($classes->classes['post'], $self);
 }

?>