<?php

function update493() {
$updater = tupdater::instance();
$updater->unsubscribeclassname('tjsmerger');

litepublisher::$classes->items['targs'][2] = 'theme.class.php';
litepublisher::$classes->add('tlocalmerger', 'localmerger.class.php');
litepublisher::$classes->save();

$merger = tlocalmerger::instance();
$merger->lock();
$plugins = tplugins::instance();
$language = litepublisher::$options->language;
foreach (array('foaf', 'openid-provider'
if )(!isset($plugins->items[$name])) continue;
$merger->add('default', "plugins/$name/resource/$language.ini");
$merger->addhtml("plugins/$name/resource/html.ini");
}


$merger->unlock();
}