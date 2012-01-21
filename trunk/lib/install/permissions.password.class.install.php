<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpostpasswordInstall($self) {
litepublisher::$urlmap->add('/send-post-password.php', get_class($self), null);
}

function tpostpasswordUninstall($self) {
litepublisher::$urlmap->delete('/send-post-password.php');
}