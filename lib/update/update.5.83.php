<?php
function update583() {
$t = ttemplate::i();
$t->heads = str_replace(
'<link rel="shortcut icon" type="image/x-icon" href="$template.icon" />',
'<link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />
<link rel="apple-touch-icon" href="$site.files/apple-touch-icon.png" />',
$t->heads);
$t->save();

if (litepublisher::$classes->exists('ulogin')) {
$js = tjsmerger::i();
$js->lock();
litepublisher::$classes->add('emailauth', 'emailauth.class.php', 'ulogin');
  $js->replacefile('default', '/plugins/ulogin/ulogin.popup.min.js', '/plugins/ulogin/resource/ulogin.popup.min.js');
  $js->replacefile('default', '/plugins/ulogin/' . litepublisher::$options->language . '.ulogin.popup.min.js',
'/plugins/ulogin/resource/' . litepublisher::$options->language . '.ulogin.popup.min.js');
$js->unlock();

  tcssmerger::i()->replacefile('default', '/plugins/ulogin/ulogin.popup.css', '/plugins/ulogin/resource/ulogin.popup.css');
}
}