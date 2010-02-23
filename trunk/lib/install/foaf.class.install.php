<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $urlmap->add('/foaf.xml', get_class($self), 'xml');
  $urlmap->add($self->redirlink, get_class($self), 'redir', 'get');
  $urlmap->unlock();
  
  if ($self->dbversion) {
      $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->createtable($self->table, file_get_contents($dir .'foaf.sql'));
  }
  
    $actions = TXMLRPCAction ::instance();
  $actions->lock();
  $actions->add('invatefriend', get_class($self), 'Invate');
  $actions->add('rejectfriend', get_class($self), 'Reject');
  $actions->add('acceptfriend', get_class($self), 'Accept');
  $actions->unlock();
}

function tfoafUninstall($self) {
  $actions = TXMLRPCAction ::instance();
  $actions->deleteclass(get_class($self));

  turlmap::unsub($self);
  
    if ($self->dbversion) {
      $manager = tdbmanager ::instance();
    $manager->deletetable($self->table);
}    

$std = tstdwidgets::instance();
  $std->delete('friends');
}

?>