<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsourcefiles  {

public function getcontent() {
$plugin = tsourcefiles::instance();
$plugin->adddir('lib');
}

public function processform() {
}
}//class
?>