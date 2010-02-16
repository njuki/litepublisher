<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function texternallinksInstall($self) {
if (dbversion) {
$manager = tdbmanager::instance();
$manager->createtable($self->table,
'id int UNSIGNED NOT NULL auto_increment,
clicked int UNSIGNED NOT NULL default 0,
url varchar(255)not null,
     PRIMARY KEY(id), 
key url (url)
');
} else {
}

$filter = tcontentfilter::instance();
$filter->afterfilter = $self->filter;
}

function texternallinksUninstall($self) {
$filter = tcontentfilter::instance();
$filter->unsubscribeclass($self);
}

?>