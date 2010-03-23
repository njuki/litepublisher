<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsourcefiles  {

public function getcontent() {
$filename = dirname(__file__) . DIRECTORY_SEPARATOR . 'files.dat';
$plugin = tsourcefiles::instance();
$plugin->adddir('lib');
$plugin->adddir('themes');
$plugin->adddir('js');
$plugin->adddir('plugins');
}

public function processform() {
}
}//class
?>