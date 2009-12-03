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
    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'comments.db.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'comments.db.raw.sql'));
    $manager->CreateTable($self->table . 'kept', file_get_contents($dir .'comments.db.kept.sql'));
}

?>