<?php

function update494() {
  litepublisher::$site->jquery_version = '1.6.4';
litepublisher::$classes->items['targs'][2] = 'theme.class.php';
litepublisher::$classes->items['tfilemerger'] = array('jsmerger.class.php', '');
litepublisher::$classes->items['tlocalmerger'] = array('localmerger.class.php', '');
litepublisher::$classes->add('tadminlocalmerger', 'admin.localmerger.class.php');
litepublisher::$classes->save();

$merger = tlocalmerger::i();
$merger->install();
$merger->lock();
$plugins = tplugins::i();
$language = litepublisher::$options->language;
foreach (array('codedoc', 'downloaditem', 'foaf', 'openid-provider', 'tickets') as $name) {
if (!isset($plugins->items[$name])) continue;
$merger->addplugin($name);
}
$merger->unlock();

$admin = tadminmenus::i();
if ($id = $admin->url2id('/admin/options/local/')) {
$admin->items[$id]['class'] = 'tadminlocalmerger';
$admin->save();
litepublisher::$urlmap->setvalue($admin->items[$id]['idurl'], 'class', 'tadminlocalmerger');
}

litepublisher::$options->version = 4.94;
litepublisher::$options->savemodified();
}
