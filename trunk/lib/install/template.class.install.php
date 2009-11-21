<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function ttemplateInstall($self) {
    //footer
    $html = &THtmlResource::instance();
    $html->section = 'installation';
$self->footer = $html->footer() . $html->stat;
}

?>