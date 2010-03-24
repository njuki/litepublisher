<?php
function update335() {
$menus = tmenus::instance();
foreach ($menus->items as $id => $item) {
$menus->items[$id]['class'] = 'tmenu';
}
$menus->save();
}
?>