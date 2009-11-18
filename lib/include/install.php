<?php
$mode = 'install';
 require_once($paths['lib'] . 'installer.class.php');
 $installer = new tinstaller();
 $installer->install();
exit();
?>