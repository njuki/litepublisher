<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
$self->infotml = '
';
$self->save();

  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'tickets.sql'));
  }

$posts = tposts::instance();
$posts->lock();
$posts->coclasses[] = get_class($self);
$posts->addcoclass(get_class($self));
//install tticket
$class = 'tticket';
    litepublisher::$classes->Add($class, 'ticket.class.php', basename(dirname(__file__) ));
$posts->unlock();
}

function tticketsUninstall($self) {
$posts->coclasses[] = get_class($self);
$posts->deletecoclass(get_class($self));
//install tticket
$class = 'tticket';
    litepublisher::$classes->delete($class);
$posts->unlock();

}

?>