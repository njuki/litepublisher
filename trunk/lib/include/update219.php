<?php

function Update219() {
 global $paths;
 @unlink($paths['lib']. 'adminbackup.php');
}
?>