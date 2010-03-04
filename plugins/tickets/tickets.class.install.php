<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
$self->infotml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'ticket.tml');
$self->save();
'
status: $ticket.state
closed: $ticket.closed
assigned to: $ticket.assignedto
priorety: $ticket.prio
version: $ticket.version
OS: $ticket.os
';



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

$linkgen = tlinkgenerator::instance();
$linkgen->post = '/[type]/[title].htm';
$linkgen->save();
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