<?php

function update474() {
litepublisher::$classes->add('tjsmerger', 'jsmerger.class.php');
litepublisher::$classes->add('tadminjsmerger', 'admin.jsmerger.class.php');

//fix install for backward
$template = ttemplate::instance();
  $template->deletefromhead($template->getjavascript('$template.jsmerger_default'));

tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
    $admin->deleteurl('/admin/views/admin/');
    $admin->createitem($admin->url2id('/admin/views/'), 
'jsmerger', 'admin', 'tadminjsmerger');

$admin->heads =  str_replace('/js/litepublisher/admin.$site.jquery_version.min.js', '$template.jsmerger_admin', $admin->heads);
$admin->unlock();

$jsmerger = tjsmerger::instance();
$jsmerger->lock();
if (litepublisher::$classes->exists('tpolls')) {
$jsmerger->add('default', '/plugins/polls/polls.client.min.js');
$jsmerger->addtext('default', 'poll', 
'$(document).ready(function() {
 if ($("*[id^=\'pollform_\']").length) { window.pollclient.init(); }
 });');
}

if (litepublisher::$classes->exists('tajaxcommentformplugin')) {
$plugin = tajaxcommentformplugin::instance();
$jsmerger->add('comments', '/plugins/ajaxcommentform/ajaxcommentform.min.js');
$jsmerger->addtext('comments', 'ajaxform', $plugin->getjs());
}

if (litepublisher::$classes->exists('tcolorpicker')) {
$jsmerger->add('admin', '/plugins/colorpicker/colorpicker.plugin.min.js');
}

if (litepublisher::$classes->exists('tdownloaditems')) {
$jsmerger->addtext('default', 'downloaditem', '$(document).ready(function() {
if ($("a[rel=\'theme\'], a[rel=\'plugin\']").length) {
$.load_script(ltoptions.files + "/plugins/downloaditem/downloaditem.min.js");
}
});');
}

if (litepublisher::$classes->exists('tgoogleanalitic')) {
$plugin = tgoogleanalitic::instance();
    if ($plugin->user != '') {
      $s = file_get_contents(litepublisher::$paths->plugins . 'googleanalitic' . DIRECTORY_SEPARATOR . 'googleanalitic.js');
      $s = sprintf($s, $plugin->user, $plugin->se);
$jsmerger->addtext('default', 'googleanalitic', $s);
}
}

$jsmerger->unlock();
}