<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tarchivesInstall($self) {
  $posts = tposts::instance();
  $posts->changed = $self->postschanged;
  if (!dbversion) $self->postschanged();
}

function tarchivesUninstall($self) {
  turlmap::unsub($self);
  tposts::unsub($self);
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
}

?>