<?php

function update_widget_links($widget) {
foreach ($widget->items as $id => &$item) {
if (isset($item['anchor'])) {
$item['text'] = $item['anchor'];
unset($item['anchor']);
}
}
$widget->save();
}

function update406() {
update_widget_links(tlinkswidget::instance());
if (isset(litepublisher::$classes->items['tbookmarkswidget'])) {
update_widget_links(tbookmarkswidget::instance());
}
}
