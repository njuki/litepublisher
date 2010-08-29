<?php
function update375() {
$widgets = twidgets::instance();
foreach ($widgets->classes as $name => $items) {
foreach ($items as $i => $item) {
$id = $item['id'];
if (!isset($widgets->items[$id])) array_delete($this->classes[$name], $i);
}
if (count($widgets->classes[$name]) == 0) unset($widgets->classes[$name]);
}
$widgets->save();

$template = ttemplate::instance();
$template->data['heads'] = array();
$template->data['adminheads'] = array();
$template->save();
}
?>