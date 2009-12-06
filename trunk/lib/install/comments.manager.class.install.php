<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcommentmanagerInstall($self) {
if (dbversion) {
  $Posts= tposts::instance();
  $Posts->deleted = $self->postdeleted;
}
}

function tcommentmanagerUninstall(&$self) {
if (dbversion)   tposts::unsub($self);
  
  $template = ttemplate::instance();
  $template->DeleteWidget(get_class($self));
}

?>