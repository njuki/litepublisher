<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'tickets.sql'));
  }

$posts = tposts::instance();
$posts->lock();
$posts->coclasses[] = get_class($self);
$posts->itemcoclasses[] = 'tticket';
$posts->unlock();
}

function tticketsUninstall($self) {

}

?>