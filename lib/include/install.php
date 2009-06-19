<?php
$mode = 'install';
 require_once($paths['lib'] . 'installerclass.php');
 $Installer = &new TInstaller();
 $Installer->Install();

exit();
?>