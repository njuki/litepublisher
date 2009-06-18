<?php

function Update217() {
 global $paths;
 $dir = $paths['data'] . 'categories';
 @mkdir($dir, 0777);
 @chmod($dir, 0777);
}
?>