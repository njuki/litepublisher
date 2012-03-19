<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tregoauth2Install($self) {
  //litepublisher::$urlmap->addget('/admin/regoauth2.php', get_class($self));
  
$self->lock();
$dirname = basename(dirname(__file__));
litepublisher::$classes->add('tregservice', 'service.class.php', $dirname);
litepublisher::$classes->add('tgoogleregservice', 'google.service.php', $dirname);

$self->add(tgoogleregservice::i());
  litepublisher::$urlmap->clearcache();
}

function tregoauth2Uninstall($self) {
foreach ($self->items as $id => $item) {
litepublisher::$classes->delete($item['class']);
//@unlink($dir . $item['class']);
}
turlmap::unsub($self);
}