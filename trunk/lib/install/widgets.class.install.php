<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twidgetsInstall($self) {
  $dir = litepublisher::$paths['data'] . 'widgets';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
}
?>