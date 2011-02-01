<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloaditemsInstall($self) {
  if (!dbversion) die("Ticket  system only for database version");
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  tlocal::loadsection('admin', 'tickets', $dir);
  $filter = tcontentfilter::instance();
  $filter->phpcode = true;
  $filter->save();
  litepublisher::$options->parsepost = false;
  
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->childtable, file_get_contents($dir .'ticket.sql'));
  $manager->addenum('posts', 'class', 'tticket');
  
  $optimizer = tdboptimizer::instance();
  $optimizer->lock();
  $optimizer->childtables[] = 'tickets';
  $optimizer->addevent('postsdeleted', 'ttickets', 'postsdeleted');
  $optimizer->unlock();
  
  litepublisher::$classes->lock();
  //install polls if its needed
  $plugins = tplugins::instance();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
  $polls = tpolls::instance();
  $polls->garbage = false;
  $polls->save();
  
  litepublisher::$classes->Add('tticket', 'ticket.class.php', basename(dirname(__file__) ));
  tticket::checklang();
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
  $ini = parse_ini_file($dir . litepublisher::$options->language . '.install.ini', false);
  
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
  
}

function tdownloaditemsUninstall($self) {
  //die("Warning! You can lost all tickets!");
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
  
  if (class_exists('tpolls')) {
    $polls = tpolls::instance();
    $polls->garbage = true;
    $polls->save();
  }
  tlocal::clearcache();
  
  $manager = tdbmanager ::instance();
  $manager->deletetable($self->childtable);
  $manager->delete_enum('posts', 'class', 'tticket');
  
  $optimizer = tdboptimizer::instance();
  $optimizer->lock();
  $optimizer->unsubscribeclass($self);
  if (false !== ($i = array_search('tickets', $optimizer->childtables))) {
    unset($optimizer->childtables[$i]);
  }
  $optimizer->unlock();
  
}

?>