<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprivatefilesInstall($self) {
$dir = litepublisher::$paths->files . 'private';
@mkdir($dir, 0777);
@chmod($dir, 0777);
}