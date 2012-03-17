<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tregoauth2Install($self) {
  litepublisher::$urlmap->addget('/admin/regoauth2.php', get_class($self));
  
$self->lock();

litepublisher::$classes->add('

  litepublisher::$urlmap->clearcache();
}

function tregoauth2Uninstall($self) {
turlmap::unsub($self);
}