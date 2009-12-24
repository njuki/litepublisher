<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TPluginsInstall(&$self) {
  global $paths;
  @mkdir($paths['data']. 'plugins', 0777);
  @chmod($paths['data']. 'plugins', 0777);
}

?>