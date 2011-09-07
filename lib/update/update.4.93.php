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
foreach (array('codedoc', 'downloaditem', 'foaf', 'openid-provider', 'tickets') as $name) {
if (!isset($plugins->items[$name])) continue;
$merger->addplugin($name);
}

$merger->unlock();
}
