<?php

function Update264() {
global $classes;
$classes->classes['archives'] = 'TArchives';
$classes->classes['menus'] = 'TMenu';
$classes->Save();

$map = TSitemap::Instance();
$map->CreateFiles();
}
?>