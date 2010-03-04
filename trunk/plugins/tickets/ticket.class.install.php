<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketInstall($self) {
$posts = tposts::instance();
$posts->itemcoclasses[] = get_class($self);
$posts->save();
}

function tticketUninstall($self) {
$class = get_class($self);
$posts = tposts::instance();
    $i = array_search($class, $posts->itemcoclasses);
    if (is_int($i)) {
      array_splice($posts->itemcoclasses, $i, 1);
      $posts->save();
}
}

?>