<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function trssfilelistInstall($self) {
  $rss = trss::instance();
  $rss->beforepost = $self->beforepost;

$jsmerger = tjsmerger::instance();
$jsmerger->addtext('default', 'rssfilelist',
'$(document).ready(function() {
var urlfile = get_cookie("rssfilelist");
if (urlfile) {
//prety
}
});'
  
  litepublisher::$urlmap->clearcache();
}

function trssfilelistUninstall($self) {
  $rss = trss::instance();
  $rss->unsubscribeclass($self);

$jsmerger = tjsmerger::instance();
$jsmerger->deletetext('default', 'rssfilelist');
  
  litepublisher::$urlmap->clearcache();
}
