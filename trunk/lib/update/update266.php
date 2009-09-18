<?php

function Update266() {
global $classes;
if (isset($classes->classes['menus'])) unset($classes->classes['menus']);
$classes->classes['menu'] = 'TMenu';
$classes->Save();
}
?>