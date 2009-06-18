<?php

function Update201() {
 global $paths;
 @unlink($paths['lib'] .'request.txt');
 @unlink($paths['lib'] . 'passw.txt');
}
?>