<?php

function TPluginsInstall(&$self) {
  global $paths;
  @mkdir($paths['data']. 'plugins', 0777);
  @chmod($paths['data']. 'plugins', 0777);
}

?>