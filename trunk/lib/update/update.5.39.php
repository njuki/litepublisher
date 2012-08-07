<?php

function replacejs($section, $src, $dst) {
$js = tjsmerger::i();
    if (!isset($js->items[$section])) return false;
    if (!($src = $js->normfilename($src))) return false;
    if (!($dst = $js->normfilename($dst))) return false;
    if (false === ($i = array_search($src, $js->items[$section]['files']))) return false;
$js->items[$section]['files'][$i] = $dst;
    $js->save();
}
  
function update539() {
$parser = tthemeparser::i();
if (!isset($parser->data['extrapaths'])) {
$parser->data['extrapaths'] = array();
$parser->save();
}

if (litepublisher::$classes->exists('tusernews')) {
$u = tusernews::i();
    if (!isset($u->data['editorfile'])) {
$u->data['dir'] = 'usernews';
    $u->data['checkspam'] = false;
    $u->data['editorfile'] = 'editor.htm';
$u->save();
}

tlocalmerger::i()->addplugin('usernews');
}

$js = tjsmerger::i();
$js->lock();
replacejs('default', '/js/litepublisher/cookie.min.js', '/js/plugins/jquery.cookie.min.js');
//$js->deletefile('default', '/js/litepublisher/cookie.min.js');
//  $js->add('default', '/js/plugins/jquery.cookie.min.js');

  $section = 'posteditor';
replacejs($section, '/js/litepublisher/swfuploader.min.js', '/js/litepublisher/uploader.min.js');
replacejs('posteditor', '/js/swfupload/swfupload.js', '/js/swfupload/swfupload.min.js');

  $lang = tlocal::admin('common');
$js->deletetext('default', 'dialog');
  $js->addtext('default', 'dialog', 
"var lang;\nif (lang == undefined) lang = {};\n" . 
sprintf('lang.dialog = %s;',  json_encode(
  array(
  'error' => $lang->error,
  'confirm' => $lang->confirm,
  'confirmdelete' => $lang->confirmdelete,
  'cancel' => $lang->cancel,
  'yes' => $lang->yesword,
  'no' => $lang->noword,
  )
  )));

$js->unlock();
}