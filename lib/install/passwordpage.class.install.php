<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpasswordpageInstall($self) {
litepublisher::$urlmap->addget('/check-password.php', get_class($self));
}

function tpasswordpageUninstall($self) {
litepublisher::$urlmap->delete('/check-password.php');
}