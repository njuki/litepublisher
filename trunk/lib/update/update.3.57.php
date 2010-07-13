<?php
function update357() {
$widgets = twidgets::instance();
$widgets->lock();

litepublisher::$classes->lock();
litepublisher::$classes->add('twidget', 'widgets.class.php');
litepublisher::$classes->add('twidgetscache', 'widgets.class.php');
litepublisher::$classes->add('tsitebars', 'widgets.class.php');
litepublisher::$classes->add('tcommentswidget', 'widgets.comments.class.php');

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

$template = ttemplate::instance();
$data = new titems();
      $data->data = $template->data['sitebars'];
unset($template->data['sitebars']);
$template->save();

$std = tstdwidgets::instance();
$sitebars = tsitebars::instance();
foreach ($data->items as $i => $sitebar) {
$j = 0;
foreach ($sitebar as $idold => $item) {
switch ($item['class']) {
case 'tcommentswidget':
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
$id = $widgets->find('tlinkswidget');
if ($id === false) {
$widget = tlinkswidget::instance();
$id = $widgets->add($widget);
}
$ajax = $std->items['links']['ajax'];
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