<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcodedocpluginInstall($self) {
  if (!dbversion) die("Ticket  system only for database version");
  tfiler::deletemask(litepublisher::$paths->languages . '*.php');
  $self->checkadminlang();
  
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->table, file_get_contents($self->resource .'ticket.sql'));
  
  litepublisher::$classes->lock();
litepublisher::$classes->add('tpostclasses', 'post.classes.php');
  $posts = tposts::instance();
  $posts->deleted = $self->postdeleted;
  
  $class = 'tticket';
  litepublisher::$classes->Add($class, 'ticket.class.php', basename(dirname(__file__) ));
  
  //install polls if its needed
  $plugins = tplugins::instance();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
  $polls = tpolls::instance();
  $polls->finddeleted = false;
  $polls->save();
  
  litepublisher::$classes->Add('tticketeditor', 'admin.ticketeditor.class.php', basename(dirname(__file__)));
  litepublisher::$classes->Add('tadmintickets', 'admin.tickets.class.php', basename(dirname(__file__)));
  
  $menus = tadminmenus::instance();
  $idmenu = $menus->createitem(0, 'tickets', 'ticket', 'tadmintickets');
  $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['tickets'];
  $idmenu = $menus->createitem($idmenu, 'editor', 'ticket', 'tticketeditor');
  $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['editortitle'];
  $menus->unlock();
  litepublisher::$classes->unlock();
  
  $linkgen = tlinkgenerator::instance();
  $linkgen->data['codedoc'] = '/codedoc/[title].htm';
  $linkgen->save();
  
  $cron = tcron::instance();
  $cron->addweekly(get_class($self), 'optimize', null);
}

function tcodedocUninstall($self) {
  //die("Warning! You can lost all tickets!");
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
  
  litepublisher::$classes->lock();
  if (litepublisher::$debug) litepublisher::$classes->delete('tpostclasses');
  tposts::unsub($self);
  
  $class = 'tticket';
  litepublisher::$classes->delete($class);
  
  
  litepublisher::$classes->delete('tticketeditor');
  litepublisher::$classes->delete('tadmintickets');
  
  $menus = tadminmenus::instance();
  $menus->lock();
  $menus->deleteurl('/admin/tickets/editor/');
  $menus->deleteurl('/admin/tickets/');
  $menus->unlock();
  litepublisher::$classes->unlock();
  
  $manager = tdbmanager ::instance();
  $manager->deletetable($self->table);
  
  $polls = tpolls::instance();
  $polls->finddeleted = true;
  $polls->save();
  
  $linkgen = tlinkgenerator::instance();
  $linkgen->post = '/[title].htm';
  $linkgen->save();
  tfiler::deletemask(litepublisher::$paths->languages . '*.php');
}

?>