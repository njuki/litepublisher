<?php
function update357() {
$widgets = twidgets::instance();
$widgets->lock();
$std = tstdwidgets::instance();

$classes = litepublisher::$classes;
$classes->lock();

$plugins = tplugins::instance();
$plugins->lock();
$plugins->delete('adminlinks');
$plugins->delete('oldestposts');
$plugins->delete('keywords');
$plugins->delete('sameposts');
$plugins->delete('sape');
$plugins->save();

$classes->add('twidget', 'widgets.class.php');
$classes->add('torderwidget', 'widgets.class.php');
$classes->add('tclasswidget', 'widgets.class.php');
$classes->add('twidgetscache', 'widgets.class.php');
$classes->add('tsitebars', 'widgets.class.php');
$classes->add('tcommentswidget', 'widget.comments.class.php');
$classes->add('tmetawidget', 'widget.meta.class.php');
$classes->add('tcommontagswidget', 'tags.common.class.php');
$classes->add('tcategorieswidget', 'tags.categories.class.php');
$classes->add('ttagswidget', 'tags.cloud.class.php');
$classes->add('tarchiveswidget', 'archives.class.php');
$classes->add('tpostswidget', 'posts.class.php');
$classes->add('tmenuwidget', 'widget.menu.class.php');
//admin
$classes->add('tadminwidget', 'admin.widget.class.php');
$classes->add('tadminmaxcount', 'admin.widget.class.php');
$classes->add('tadminshowcount', 'admin.widget.class.php');
$classes->add('tadminorderwidget', 'admin.widget.class.php');
$classes->add('tadminfriendswidget', 'admin.widget.class.php');
$classes->add('tadmintagswidget', 'admin.widget.class.php');
$classes->add('tadmincustomwidget', 'admin.widget.class.php');
$classes->add('tadminlinkswidget', 'admin.widget.class.php');
$classes->add('tadminmetawidget', 'admin.widget.class.php');
$classes->add('tadminhomewidgets', 'admin.widget.class.php');
//change filename
$classes->items['tlinkswidget'][0] = 'widget.links.class.php';
$classes->items['tcustomwidget'][0] = 'widget.custom.class.php';
$classes->items['tcommentswidget'][0] = 'widget.comments.class.php';

$custom = tcustomwidget::instance();
$customitems = array();
$items = array();
foreach ($custom->items as $idold => $item) {
$item['template'] = $item['templ'] ? 'widget' : '';
unset($item['templ']);
$id = $widgets->addext($custom, $item['title'], $item['template']);
$items[$id] = $item;
$customitems[$idold] = $id;
}
$custom->items = $items;
$custom->save();

$widget = tlinkswidget::instance();
foreach ($widget->items as $id => $item) {
$item['anchor'] = $item['text'];
unset($item['text']);
$widget->items[$id] = $item;
}
$widget->save();

$meta = tmetawidget::instance();
$meta->data['meta'] = $std->data['meta'];
$meta->save();

$widget = tcategorieswidget::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount);
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();

$widget = ttagswidget::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount);
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();

$widget = tarchiveswidget::instance();
$arch= tarchives::instance();
$widget->showcount = $arch->showcount;
unset($arch->data['showcount']);
$arch->save();

$widget = tpostswidget::instance();
$posts = tposts::instance();
$widget->maxcount = $posts->recentcount;
unset($posts->data['recentcount']);
$posts->save();

$widget = tcommentswidget::instance();
    $manager = tcommentmanager::instance();
if ($widget->maxcount != $manager->recentcount) {
$widget->maxcount = $manager->recentcount;
$widget->save();
}
unset($manager->data['recentcount']);
$manager->save();

$foaf = tfoaf::instance();
litepublisher::$urlmap->lock();
litepublisher::$urlmap->delete($foaf->redirlink);
$classes->add('tfriendswidget', 'widgets.friends.class.php');
litepublisher::$urlmap->unlock();

    unset($foaf->data['maxcount']);
    unset($foaf->data['redir']);
    unset($foaf->data['redirlink']);
$foaf->save();

$template = ttemplate::instance();
$data = new titems();
      $data->data = $template->data['sitebars'];
unset($template->data['sitebars']);
foreach ($template->events as $$name => $event) {
if (!isset($template->eventnames[$name])) unset($template->events[$name]);;
}
$template->save();

$sitebars = tsitebars::instance();
foreach ($data->items as $i => $sitebar) {
$j = 0;
foreach ($sitebar as $idold => $item) {
$class = $item['class'];
if ($class == 'tstdwidgets') {
$class = $std->getname($idold);
}
switch ($class) {
case 'tcommentswidget':
case 'comments':
$id = $widgets->find('tcommentswidget');
if ($id === false) {
$widget = tcommentswidget::instance();
$id = $widgets->add($widget);
}
$ajax = $std->items['comments']['ajax'];
break;

case 'tcustomwidget':
$id = $customitems[$idold];
$ajax = false;
break;

case 'tlinkswidget':
case 'links':
$id = $widgets->find('tlinkswidget');
if ($id === false) {
$widget = tlinkswidget::instance();
$id = $widgets->add($widget);
}
$ajax = $std->items['links']['ajax'];
break;

case 'meta':
$id = $widgets->add($meta);
$ajax = $std->items['meta']['ajax'];
break;

case 'tcategories':
case 'categories':
$id = $widgets->add(tcategorieswidget::instance());
$ajax = $std->items['categories']['ajax'];
break;

case 'ttags':
case 'tags':
$id = $widgets->add(ttagswidget::instance());
$ajax = $std->items['tags']['ajax'];
break;

case 'tarchives':
case 'archives':
$id = $widgets->add(tarchiveswidget ::instance());
$ajax = $std->items['archives']['ajax'];
break;

case 'tposts':
case 'posts':
$id = $widgets->add(tpostswidget ::instance());
$ajax = $std->items['posts']['ajax'];
break;

case 'friends':
case 'tfoaf':
$id = $widgets->add(tfriendswidget::instance());
$ajax = $std->items['friends']['ajax'];
break;
}

$sitebars->insert($id, $ajax, $i, $j++);
}
}

  $xmlrpc = TXMLRPC::instance();
$xmlrpc->lock();
  $xmlrpc->deleteclass('tstdwidgets');
  $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($widgets));
$xmlrpc->unlock();

$classes->delete('tstdwidgets');
$classes->unlock();
$widgets->unlock();

$admin = tadminmenus::instance();
$admin->lock();
$idwidgets = $admin->url2id('/admin/widgets/');
$admin->deleteurl('/admin/widgets/std/');
$admin->deleteurl('/admin/widgets/stdoptions/');
$admin->deleteurl('/admin/widgets/links/');
$admin->deleteurl('/admin/widgets/custom/');
$admin->deleteurl('/admin/widgets/meta/');
$admin->deleteurl('/admin/widgets/homepagewidgets/');

   //$admin->createitem($idwidgets, 'classes', 'admin', 'tadminwidgets');
   $admin->createitem($idwidgets, 'home', 'admin', 'tadminwidgets');
    $admin->createitem($idwidgets, 'addcustom', 'admin', 'tadminwidgets');
$admin->unlock();

ttheme::clearcache();

//delete files
$lib = litepublisher::$paths->lib;
$install = $lib . 'install' . DIRECTORY_SEPARATOR;

@unlink($lib . 'widgets.standarts.class.php');
@unlink($install . 'widgets.standarts.class.install.php');

@unlink($lib . 'widgets.links.class.php');
@unlink($install . 'widgets.links.class.install.php');

@unlink($lib . 'widgets.custom.class.php');
@unlink($install . 'widget.custom.class.install.php');

@unlink($lib . 'widgets.comments.class.php');
}
?>