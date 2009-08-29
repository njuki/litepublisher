<?php

function Update257() {
global $classes, $Options;
$Options->commentsdisabled = false;

$classes->Add('TManifest', 'manifest.php');
}
?>