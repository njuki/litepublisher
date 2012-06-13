<?php

function update532() {
$js = tjsmerger::i();
$js->lock();
  $section = 'admin';
  $JS->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
  $JS->add($section, '/js/litepublisher/filebrowser.min.js');
  $JS->add($section, '/js/litepublisher/admin.min.js');
  
  $section = 'POSTEDITOR';
  $JS->add($section, '/js/swfupload/swfupload.js');
  $JS->add($section, '/js/litepublisher/swfuploader.min.js');
  $JS->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.progressbar.min.js');
  $JS->add($section, '/js/litepublisher/POSTEDITOR.min.js');

$js->unlock();
}