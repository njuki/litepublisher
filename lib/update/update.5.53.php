<?php

function update553() {
tcssmerger::i()->save();

$js = tjsmerger::i();
$js->lock();
$section = 'posteditor';
  $js->add($section, '/js/plugins/filereader.min.js');
  $js->add($section, '/js/litepublisher/uploader.html.min.js');
  $js->add($section, '/js/litepublisher/uploader.flash.min.js');

  $lang =tlocal::admin('editor');
  $js->addtext($section, 'drag', "lang.posteditor.dragfiles='$lang->dragfiles';");
$js->unlock();

$css = tcssmerger::i();
$css->lock();
$section = 'admin';
  $css->add($section, '/js/jquery/ui-$site.jqueryui_version/redmond/jquery-ui-$site.jqueryui_version.custom.min.css');
  $css->add($section, '/js/litepublisher/css/fileman.min.css');
  $css->add($section, '/js/litepublisher/css/admin.views.min.css');
$css->unlock();

tupdater::i()->onupdated = $css->save;

$admin = tadminmenus::i();
$admin->heads = str_replace(
'$site.files/js/jquery/ui-$site.jqueryui_version/redmond/jquery-ui-$site.jqueryui_version.custom.min.css',
'$site.files$template.cssmerger_admin',
$admin->heads);
$admin->save();

$lm = tlocalmerger::i();
$lm->lock();
  $lm->addhtml('lib/languages/posteditor.ini');
$lm->unlock();
}