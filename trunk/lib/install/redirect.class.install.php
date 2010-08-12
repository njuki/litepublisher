<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tredirectorInsttall($self) {
$self->lock();
$self->add('rss/', '/rss.xml');
$self->add('/rss', '/rss.xml');
$self->add('/feed/', '/rss.xml');
$self->add('/contact.php', '/kontakty.htm');
$self->add('/kontakty.htm', '/contact.htm');
$self->unlock();
}
?>