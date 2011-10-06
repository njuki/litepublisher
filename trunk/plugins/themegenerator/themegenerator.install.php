<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tthemegeneratorInstall($self) {
litepublisher::$urlmap->add('/theme-generator.htm', get_class($self), null);
}

function tthemegeneratorUninstall($self) {
turlmap::unsub($self);
}