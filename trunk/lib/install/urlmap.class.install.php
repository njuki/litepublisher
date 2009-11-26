<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function turlmapInstall($self) {
if (!dbversion) return;
    $manager = tdbmanager ::instance();
    $manager->CreateTable('urlmap', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'urlmap.sql'));

}
?>