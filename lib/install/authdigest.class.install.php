<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tauthdigestInstall($self) {
  if (empty($_SERVER['HTTP_REFERER']) && isset($_POST) && (count($_POST) > 0) ) {
    $self->xxxcheck = false;
    $self->save();
  }
}
?>