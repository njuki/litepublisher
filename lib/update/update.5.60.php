<?php

function update560() {
  litepublisher::$site->jquery_version = '1.10.1';
litepublisher::$site->jqueryui_version = '1.10.3';

litepublisher::$classes->add('adminitems', 'admin.items.class.php');
litepublisher::$classes->save();

if (!isset(litepublisher::$urlmap->data['disabledcron'])) {
litepublisher::$urlmap->data['disabledcron'] = false;
litepublisher::$urlmap->save();
}

$js = tjsmerger::i();
$js->lock();
  $language = litepublisher::$options->language;
$js->add('admin', 'js/litepublisher/calendar.min.js');
  $js->add('admin', "/lib/languages/$language/admin.min.js");
$js->unlock();
}