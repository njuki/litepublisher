<?php

function update494() {
litepublisher::$classes->items['targs'][2] = 'theme.class.php';
litepublisher::$classes->items['tfilemerger'] = array('jsmerger.class.php', '');
litepublisher::$classes->items['tlocalmerger'] = array('localmerger.class.php', '');
litepublisher::$classes->add('tadminlocalmerger', 'admin.localmerger.class.php');
litepublisher::$classes->save();

$merger = tlocalmerger::i();
$merger->lock();
$merger->install();
$plugins = tplugins::i();
$language = litepublisher::$options->language;
foreach (array('codedoc', 'downloaditem', 'foaf', 'openid-provider', 'tickets') as $name) {
if (!isset($plugins->items[$name])) continue;
$merger->addplugin($name);
}

$merger->unlock();

$admin = tadminmenus::i();
$id = $admin->url2id('/admin/optons/local/');
$admin->items[$id]['class'] = 'tadminlocalmerger';
$admin->save();
}
