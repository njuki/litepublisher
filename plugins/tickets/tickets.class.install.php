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
  
  litepublisher::$options->parsepost = false;
  
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->childstable, file_get_contents($self->resource .'ticket.sql'));
  
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
  litepublisher::$classes->Add('tticketsmenu', 'tickets.menu.class.php', basename(dirname(__file__) ));
  litepublisher::$classes->Add('tticketeditor', 'admin.ticketeditor.class.php', basename(dirname(__file__)));
  litepublisher::$classes->Add('tadmintickets', 'admin.tickets.class.php', basename(dirname(__file__)));
  
  litepublisher::$options->reguser = true;
  $adminoptions = tadminoptions::instance();
  $adminoptions->usersenabled = true;
  
  $adminmenus = tadminmenus::instance();
  $adminmenus->lock();
  $parent = $adminmenus->createitem(0, 'tickets', 'ticket', 'tadmintickets');
  $adminmenus->items[$parent]['title'] = tlocal::$data['tickets']['tickets'];
  
  $idmenu = $adminmenus->createitem($parent, 'opened', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['opened'];
  
  $idmenu = $adminmenus->createitem($parent, 'fixed', 'ticket', 'tadmintickets');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['ticket']['fixed'];
  
  $idmenu = $adminmenus->createitem($parent, 'editor', 'ticket', 'tticketeditor');
  $adminmenus->items[$idmenu]['title'] = tlocal::$data['tickets']['editortitle'];
  
  $adminmenus->unlock();
  
  $menus = tmenus::instance();
  $menus->lock();
  $ini = parse_ini_file($self->resource . litepublisher::$options->language . '.install.ini', false);
  
  $menu = tticketsmenu::instance();
  $menu->type = 'tickets';
  $menu->url = '/tickets/';
  $menu->title = $ini['tickets'];
  $menu->content = $ini['contenttickets'];
  $id = $menus->add($menu);
  
  foreach (array('bug', 'feature', 'support', 'task') as $type) {
    $menu = tticketsmenu::instance();
    $menu->type = $type;
    $menu->parent = $id;
    $menu->url = "/$type/";
    $menu->title = $ini[$type];
    $menu->content = '';
    $menus->add($menu);
  }
  $menus->unlock();
  
  litepublisher::$classes->unlock();
  
  $linkgen = tlinkgenerator::instance();
  $linkgen->data['ticket'] = '/[type]/[title].htm';
  $linkgen->save();
  
  $groups = tusergroups  ::instance();
  $groups->lock();
  $groups->add('ticket', '/admin/tickets/editor/');
  $groups->defaultgroup = 'ticket';
  $groups->onhasright = $self->hasright;
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
  
  litepublisher::$classes->delete('tticket');
  litepublisher::$classes->delete('tticketeditor');
  litepublisher::$classes->delete('tadmintickets');
  
  $adminmenus = tadminmenus::instance();
  $adminmenus->lock();
  $adminmenus->deleteurl('/admin/tickets/editor/');
  $adminmenus->deleteurl('/admin/tickets/');
  $adminmenus->unlock();
  
  $menus = tmenus::instance();
  $menus->lock();
  foreach (array('bug', 'feature', 'support', 'task') as $type) {
    $menus->deleteurl("/$type/");
  }
  $menus->deleteurl('/tickets/');
  $menus->unlock();
  
  litepublisher::$classes->delete('tticketsmenu');
  litepublisher::$classes->unlock();
  
  $manager = tdbmanager ::instance();
  $manager->deletetable($self->childstable);
  
if (class_exists('tpolls')) {
  $polls = tpolls::instance();
  $polls->garbage = true;
  $polls->save();
  }
  tfiler::deletemask(litepublisher::$paths->languages . '*.php');
}

?>