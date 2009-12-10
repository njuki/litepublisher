<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcommentswidgetInstall($self) {
$manager = tcommentmanager::instance();
$manager->changed = $self->changed;
}

function tcommentswidgetUninstall($self) {
$manager = tcommentmanager::instance();
$manager->unsubscribeclass($self);
}

?>