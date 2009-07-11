<?php

function TMenuInstall(&$self) {
  global $paths;
  @mkdir($paths['data']. 'menus', 0777);
  @chmod($paths['data']. 'menus', 0777);
}

function  TMenuUninstall(&$self) {
  //rmdir(. 'menus');
}

?>