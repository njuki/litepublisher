<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tjsonserverInstall($self) {
litepublisher::$urlmap->addget($self->url, get_class($self));
}

function tjsonserverUninstall($self) {
turlmap::unsub($self);
}