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

}