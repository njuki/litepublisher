<?php
function Update277() {
global $paths;
$filename = $paths['data'] . 'template.pda.php';
$d = unserialize(PHPUncomment(file_get_contents($filename))));
$d['widgets'] = array();
$d['sitebars'] = array();
file_put_contents($filename, PHPComment(serialize($d));
}
?>