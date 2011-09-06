<?php

function update481() {
litepublisher::$classes->items['tfakemenu'] = array('menu.class.php', '');
litepublisher::$classes->save();

tlocal::loadlang('admin');
$admin = tadminmenus::instance();
$admin->lock();
    $id = $admin->createitem($admin->url2id('/admin/menu/'),
'editfake', 'editor', 'tadminmenumanager');
    $admin->items[$id]['title'] = tlocal::$data['menu']['addfake'];
$admin->unlock();

$menuclass = 'tmenus';
if (litepublisher::$classes->exists('tcategoriesmenu')) {
$plugin = tcategoriesmenu::instance();
  $template = ttemplate::instance();
  $template->unsubscribeclass($plugin);
$menuclass = 'tcategoriesmenu';
}

$views = tviews::instance();
$views->lock();
foreach ($views->items as &$viewitem) {
$viewitem['menuclass'] = $menuclass;
}
$views->items[$views->defaults['admin']]['menuclass'] = 'tadminmenus';

$views->unlock();
}