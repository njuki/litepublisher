<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcommentmanagerInstall($self) {
global $classes;
$classes->classes['commentmanager'] = get_class($self);
  $Posts= tposts::instance();
  $Posts->deleted = $self->postdeleted;
}

function tcommentmanagerUninstall(&$self) {
  tposts::unsub($self);
  
  $template = ttemplate::instance();
  $template->DeleteWidget(get_class($self));
}

?>