<?php

function update560() {
litepublisher::$classes->add('adminitems', 'admin.items.class.php');
litepublisher::$classes->save();


$js = tjsmerger::i();
$js->lock();
  $language = litepublisher::$options->language;
$js->add('admin', 'js/litepublisher/calendar.min.js');
  $js->add('admin', "/lib/languages/$language/admin.min.js");
$js->unlock();
}