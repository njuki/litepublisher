<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tmenusInstall($self) {
  global $paths;
  @mkdir($paths['data']. 'menus', 0777);
  @chmod($paths['data']. 'menus', 0777);
}

function  tmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>