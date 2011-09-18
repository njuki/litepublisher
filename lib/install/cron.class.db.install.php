<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcronInstall($self) {
  $manager = tdbmanager ::i();
  $manager->CreateTable('cron', file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'cron.sql'));
  
  $self->addnightly('tdboptimizer', 'optimize', null);
}

?>