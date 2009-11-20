<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

$mode = 'install';
 require_once($paths['lib'] . 'installer.class.php');
 $installer = new tinstaller();
 $installer->install();
exit();
?>