<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function teventsInstall($self) {
  if(get_class($self) != 'tevents') return;
  if(dbversion) {
    $manager = TDBManager ::instance();
    $manager->CreateTable('events', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'events.sql'));
  }
}

function teventsUninstall($self) {
global $options;
$options->delete($self->basename);
}
?>