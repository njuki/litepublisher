<?php

function update559() {
litepublisher::$classes->items['poststatus'] = array('kernel.admin.php', '', 'admin.posteditor.class.php');
litepublisher::$classes->save();
$lm = tlocalmerger::i();
$lm->lock();
    $language = litepublisher::$options->language;
if (litepublisher::$classes->exists('ttickets')) {
$name = 'tickets';
    $lm->deletefile('admin', "plugins/$name/resource/$language.admin.ini");
    $lm->deletehtml("plugins/$name/resource/html.ini");
}

if (litepublisher::$classes->exists('tdownloaditems')) {
$name = 'downloaditem';
    $lm->deletefile('admin', "plugins/$name/resource/$language.admin.ini");
    $lm->deletehtml("plugins/$name/resource/html.ini");
}

$lm->unlock();

$js = tjsmerger::i();
$js->lock();
  $section = 'default';
$js->deletetext($section, 'widgetlang');
$js->deletetext($section, 'dialog');
  $js->add($section, "/lib/languages/$language/default.min.js");

  $section = 'comments';
$js->deletetext($section, 'lang');
  $js->add($section, "/lib/languages/$language/comments.min.js");

  $section = 'posteditor';
$js->deletetext($section, 'lang');
  $js->add($section, "/lib/languages/$language/posteditor.min.js");

$js->unlock();
}