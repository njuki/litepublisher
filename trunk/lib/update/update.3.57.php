<?php
function update357() {
$widgets = twidgets::instance();
$widgets->lock();
$std = tstdwidgets::instance();

$classes = litepublisher::$classes;
$classes->lock();
$classes->add('twidget', 'widgets.class.php');
$classes->add('twidgetscache', 'widgets.class.php');
$classes->add('tsitebars', 'widgets.class.php');
$classes->add('tcommentswidget', 'widgets.comments.class.php');
$classes->add('tmetawidget', 'widgets.meta.class.php');
$classes->add('tcommontagswidget', 'tags.common.class.php');
$classes->add('tcategorieswidget', 'tags.categories.class.php');
$classes->add('ttagswidget', 'tags.cloud.class.php');
$classes->add('tarchiveswidget', 'archives.class.php');

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

$meta = tmetawidget::instance();
$meta->data['meta'] = $std->data['meta'];
$meta->save();

$widget = tcategories::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount) {
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();

$widget = ttagswidget::instance();
$widget->setparams($widget->owner->sortname, $widget->owner->maxcount, $widget->owner->showcount) {
unset($widget->owner->data['sortname']);
unset($widget->owner->data['maxcount']);
unset($widget->owner->data['showcount']);
$widget->owner->save();


$widget = tarchiveswidget::instance();
$arch= tarchives::instance();
$widget->showcount = $arch->showcount;
unset($arch->data['showcount']);
$arch->save();

$template = ttemplate::instance();
$data = new titems();
      $data->data = $template->data['sitebars'];
unset($template->data['sitebars']);
$template->save();

$sitebars = tsitebars::instance();
foreach ($data->items as $i => $sitebar) {
$j = 0;
foreach ($sitebar as $idold => $item) {
$class = item['class'];
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
$id = $widgets->add(tcategorieswidget::instance();
$ajax = $std->items['categories']['ajax'];
break;

case 'ttags':
case 'tags':
$id = $widgets->add(ttagswidget::instance();
$ajax = $std->items['tags']['ajax'];
break;

case 'tarchives':
case 'archives':
$id = $widgets->add(tarchiveswidget ::instance();
$ajax = $std->items['archives']['ajax'];
break;

}

$sitebars->insert($id, $ajax, $i, $j++);
}
}

litepublisher::$classes->delete('tstdwidgets');
litepublisher::$classes->unlock();

$widgets->unlock();
ttheme::clearcache();
}
?>