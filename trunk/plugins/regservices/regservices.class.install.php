<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tregservicesInstall($self) {
$dir = litepublisher::$paths->data . 'regservices';
@mkdir($dir, 0777);
@chmod($dir, 0777);

$about = tplugins::getabout(tplugins::getname(__file__));
$self->lock();
$self->widget_title  = sprintf('<h4>%s</h4>', $about['widget_title']);
$name = basename(dirname(__file__));
litepublisher::$classes->add('tregservice', 'service.class.php', $name);
litepublisher::$classes->add('tgoogleregservice', 'google.service.php', $name);
litepublisher::$classes->add('tfacebookregservice', 'facebook.service.php', $name);

$self->add(tgoogleregservice::i());
$self->add(tfacebookregservice::i());
$self->unlock();

 litepublisher::$urlmap->addget($self->url, get_class($self));
  litepublisher::$urlmap->clearcache();

tadminlogin::i()->oncontent = $self->oncontent;
tadminreguser::i()->oncontent = $self->oncontent;
}

function tregservicesUninstall($self) {
tadminlogin::i()->unbind($self);
tadminreguser::i()->unbind($self);

turlmap::unsub($self);
foreach ($self->items as $id => $item) {
litepublisher::$classes->delete($item['class']);
}


tfiler::delete(litepublisher::$paths->data . 'regservices');
}