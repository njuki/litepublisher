<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$mode = 'install';
require_once($paths['lib'] . 'installer.class.php');
if (defined('debug')) {
  global $paths;
  require_once($paths['lib'] . 'filer.class.php');
  tfiler::delete($paths['data'], true, true);
}
$installer = new tinstaller();
$installer->install();

global $options;
if (is_a($options, 'toptions')) $options->savemodified();
exit();
?>