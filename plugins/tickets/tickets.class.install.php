<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
  if (!dbversion) die("Ticket  system only for database version");
  tfiler::deletemask(litepublisher::$paths->languages . '*.php');
  $self->checkadminlang();
  
  $filter = tcontentfilter::instance();
  $filter->phpcode = true;
  $filter->save();
  
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->ticketstable, file_get_contents($self->resource .'ticket.sql'));
  
  litepublisher::$classes->lock();
  litepublisher::$classes->add('tpostclasses', 'post.classes.php');
  $posts = tposts::instance();
  $posts->deleted = $self->postdeleted;
  
  //install polls if its needed
  $plugins = tplugins::instance();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
  $polls = tpolls::instance();
  $polls->garbage = false;
  $polls->save();
  
  litepublisher::$classes->Add('tticket', 'ticket.class.php', basename(dirname(__file__) ));
  litepublisher::$classes->Add('tticketeditor', 'admin.ticketeditor.class.php', basename(dirname(__file__)));
  litepublisher::$classes->Add('tadmintickets', 'admin.tickets.class.php', basename(dirname(__file__)));
  
  $menus = tadminmenus::instance();
  litepublisher::$options->reguser = true;
  $adminoptions = tadminoptions::instance();
  $adminoptions->usersenabled = true;
  
  $idmenu = $menus->createitem(0, 'tickets', 'ticket', 'tadmintickets');
  $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['tickets'];
  $idmenu = $menus->createitem($idmenu, 'editor', 'ticket', 'tticketeditor');
  $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['editortitle'];
  $menus->unlock();
  litepublisher::$classes->unlock();
  
  $linkgen = tlinkgenerator::instance();
  $linkgen->data['ticket'] = '/[type]/[title].htm';
  $linkgen->save();
  
  $groups = tusergroups  ::instance();
  $groups->lock();
  $groups->add('ticket', '/admin/tickets/editor/');
  $groups->defaultgroup = 'ticket';
  $groups->unlock();
  
  $cron = tcron::instance();
  $cron->addweekly(get_class($self), 'optimize', null);
}

function tticketsUninstall($self) {
  //die("Warning! You can lost all tickets!");
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
  
  litepublisher::$classes->lock();
  //if (litepublisher::$debug) litepublisher::$classes->delete('tpostclasses');
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
  $manager->deletetable($self->ticketstable);
  
  $polls = tpolls::instance();
  $polls->garbage = true;
  $polls->save();
  
  tfiler::deletemask(litepublisher::$paths->languages . '*.php');
}

?>