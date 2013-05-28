<?php

function update560() {
litepublisher::$classes->add('adminitems', 'admin.items.class.php');
litepublisher::$classes->save();

$js = tjsmerger::i();
$js->lock();
$js->add('admin', 'js/litepublisher/calendar.min.js');
$js->unlock();
}