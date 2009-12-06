<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcommentswidgetInstall($self) {
if (!dbversion) {
$manager = tcommentmanager::instance();
$manager->changed = $self->changed;
}
}

function tcommentswidgetUninstall($self) {
if (!dbversion){
$manager = tcommentmanager::instance();
$manager->unsubscribeclass($self);
}
}

?>