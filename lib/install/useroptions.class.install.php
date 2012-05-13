<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tuseroptionsInstall($self) {
$self->defvalues['subscribe'] = litepublisher::$options->defaultsubscribe ? 'enabled' : 'disabled';
$self->save();

  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'useroptions.sql'));
}