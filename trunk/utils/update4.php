<?php
define('litepublisher_mode', 'xmlrpc');
include('index.php');
if (isset(litepublisher::$site)) {
require(litepublisher::$paths->lib . 'update' . DIRECTORY_SEPARATOR  . 'update.4.php');
update4();
} else {
tupdater::instance()->autoupdate();
}