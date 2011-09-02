<?php

function update493() {
$updater = tupdater::instance();
$updater->unsubscribeclassname('tjsmerger');

litepublisher::$classes->items['targs'][2] = 'theme.class.php';
litepublisher::$classes->add('tlocalmerger', 'localmerger.class.php');
litepublisher::$classes->save();

if (litepublisher::$classes->exists('tticket')) {
}
}