<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tcommentsInstall($self) {
global $classes;
$classes->classes['commentmanager'] = get_class($self);
$self->options = array('recentcount' =>  7,
'SendNotification' =>  true);

    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'comments.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'raw.sql'));
}

?>