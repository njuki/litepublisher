<?php
function update357() {
litepublisher::$classes->lock();
litepublisher::$classes->add('twidget', 'widgets.class.php');
litepublisher::$classes->add('twidgetscache', 'widgets.class.php');
litepublisher::$classes->add('tsitebars', 'widgets.class.php');
litepublisher::$classes->add('tcommentswidget', 'widgets.comments.class.php');


$template = ttemplate::instance();
$data = new titems();
      $data->data = $template->data['sitebars'];
unset($template->data['sitebars']);
$template->save();

$std = tstdwidgets::instance();
$widgets = twidgets::instance();
$widgets->lock();
$sitebars = tsitebars::instance();
foreach ($data->items as $i => $sitebar) {
$j = 0;
foreach ($sitebar as $item) {
switch ($item['class']) {
case 'tcommentswidget':
$id = $widgets->find('tcommentswidget');
if ($id === false) {
$widget = tcommentswidget::instance();
$id = $widgets->add($widget);
}
$ajax = $std->items['comments']['ajax'];
break;
}

$sitebars->insert($id, $ajax, $i, $j++);
}
}

$widgets->unlock();

litepublisher::$classes->delete('tstdwidgets');
litepublisher::$classes->unlock();

ttheme::clearcache();
}
?>