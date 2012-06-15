<?php

function update532() {
litepublisher::$site->jqueryui_version = '1.8.21';
litepublisher::$site->save();

  $css = tcssmerger::i();
$css->lock();
$css->delete('default', '/js/litepublisher/prettyphoto.dialog.css');
$css->add('default', '/js/litepublisher/css/prettyphoto.dialog.css');
$css->unlock();

$lang = tlocal::admin();
$js = tjsmerger::i();
$js->lock();

$langjs = "var lang;\nif (lang == undefined) lang = {};\n";
  $js->addtext('default', 'dialog', $langjs . sprintf('lang.dialog = %s;',  json_encode(
  array(
  'error' => $lang->error,
  'confirm' => $lang->confirm,
  'confirmdelete' => $lang->confirmdelete,
  'cancel' => $lang->cancel,
  'yes' => $lang->yesword,
  'no' => $lang->noword,
  )
  )));

  $section = 'admin';
  $JS->delete($section, '/js/litepublisher/filebrowser.min.js');

  $section = 'POSTEDITOR';
  $JS->add($section, '/js/swfupload/swfupload.js');
  $JS->add($section, '/js/litepublisher/swfuploader.min.js');
  $JS->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.progressbar.min.js');
  $JS->add($section, '/js/plugins/mustache.min.js');
  $JS->add($section, '/js/litepublisher/POSTEDITOR.min.js');
  $js->addtext($section, 'lang', $js . sprintf('lang.posteditor = %s;',  json_encode(
  array(
'emptytitle' => tlocal::get('editor', 'emptytitle'),
'upload' => tlocal::i()->upload,
'add' => $lang->add,
'del' => $lang->delete,
'property' => $lang->property,
  )
  )));


$js->unlock();
}