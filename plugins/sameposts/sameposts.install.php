<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tsamepostsInstall($self) {$Template = &TTemplate::Instance();
$template = ttemplate::instance();
$template->AddOnSitebarClass('tpost', $self->onsitebar);

  $posts= tposts::instance();
  $posts->changed = $self->postschanged;
 }
 
function tsamepostsUninstall($self) {
  tposts::unsub($self);
$template = ttemplate::instance();
$template->DeleteClassSitebar($self);
 }

?>