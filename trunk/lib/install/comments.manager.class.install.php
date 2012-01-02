<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentmanagerInstall($self) {
  $Posts= tposts::i();
  $Posts->deleted = $self->postdeleted;
}

function tcommentmanagerUninstall(&$self) {
  tposts::unsub($self);
  
  $template = ttemplate::i();
  $template->DeleteWidget(get_class($self));
}

?>