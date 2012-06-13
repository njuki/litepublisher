<?php

function update532() {
litepublisher::$site->jqueryui_version = '1.8.21';
litepublisher::$site->save();

$lang = tlocal::admin();
$js = tjsmerger::i();
$js->lock();
  $section = 'admin';
  $JS->delete($section, '/js/litepublisher/filebrowser.min.js');
  $js->addtext($section, 'lang', $js . sprintf('lang.admin = %s;',  json_encode(
  array(
'emptytitle' => tlocal::get('editor', 'emptytitle'),
  )
  )));

  $section = 'POSTEDITOR';
  $JS->add($section, '/js/swfupload/swfupload.js');
  $JS->add($section, '/js/litepublisher/swfuploader.min.js');
  $JS->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.progressbar.min.js');
  $JS->add($section, '/js/litepublisher/POSTEDITOR.min.js');

$js->unlock();
}