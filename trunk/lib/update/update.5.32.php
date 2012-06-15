<?php

function update532() {
litepublisher::$site->jqueryui_version = '1.8.21';
litepublisher::$site->save();

litepublisher::$classes->add('tjsonfiles', 'json.files.class.php');

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
  $js->delete($section, '/js/litepublisher/filebrowser.min.js');

  $section = 'posteditor';
  $js->add($section, '/js/swfupload/swfupload.js');
  $js->add($section, '/js/litepublisher/swfuploader.min.js');
  $js->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery.ui.progressbar.min.js');
  $js->add($section, '/js/plugins/mustache.min.js');
  $js->add($section, '/js/litepublisher/posteditor.min.js');
  $js->add($section, '/js/litepublisher/fileman.min.js');
  $js->add($section, '/js/litepublisher/fileman.templates.min.js');
  $js->addtext($section, 'lang', sprintf('lang.posteditor = %s;',  json_encode(
  array(
'emptytitle' => tlocal::get('editor', 'emptytitle'),
'upload' => tlocal::i()->upload,
'currentfiles' => $lang->currentfiles,
'newupload' => $lang->newupload,
'add' => $lang->add,
'del' => $lang->delete,
'property' => $lang->property,
'title' => $lang->title,
'description' => $lang->description,
'keywords' => $lang->keywords,
'file' => $lang->file,
'filesize' => $lang->filesize
  )
  )));
$js->unlock();
}