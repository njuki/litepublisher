<?php

function update533() {
$lang = tlocal::admin('editor');
$js = tjsmerger::i();
$js->lock();
  $section = 'admin';
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
  $js->add($section, '/js/litepublisher/admin.min.js');

$js->unlock();
}