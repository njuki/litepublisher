<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcodedocpluginInstall($self) {
  if (!dbversion) die("Ticket  system only for database version");
  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, '
  id int unsigned NOT NULL default 0,
  parent int unsigned NOT NULL default 0,
  class varchar(32) NOT NULL,
  KEY id (id)
  ');
  
  $merger = tlocalmerger::i();
  $merger->addplugin(tplugins::getname(__file__));
  
  $posts = tposts::i();
  $posts->lock();
  $posts->deleted = $self->postdeleted;
  $posts->added = $self->postadded;
  $posts->unlock();
  
  litepublisher::$classes->lock();
  litepublisher::$classes->Add('tcodedocfilter', 'codedoc.filter.class.php', basename(dirname(__file__) ));
  litepublisher::$classes->Add('tcodedocmenu', 'codedoc.menu.class.php', basename(dirname(__file__) ));
  
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->seteventorder('beforecontent', $self, 0);
  $plugins = tplugins::i();
  if (!isset($plugins->items['wikiwords'])) $plugins->add('wikiwords');
  $filter->unlock();
  
  $about = tplugins::localabout(dirname(__file__));
  $menu = tcodedocmenu::i();
  $menu->url = '/doc/';
  $menu->title = $about['menutitle'];
  
  $menus = tmenus::i();
  $menus->add($menu);
  
  litepublisher::$classes->unlock();
  
  $linkgen = tlinkgenerator::i();
  $linkgen->data['codedoc'] = '/doc/[title].htm';
  $linkgen->save();
}

function tcodedocpluginUninstall($self) {
  //die("Warning! You can lost all tickets!");
  litepublisher::$classes->lock();
  if (litepublisher::$debug) litepublisher::$classes->delete('tpostclasses');
  tposts::unsub($self);
  
  $menus = tmenus::i();
  $menus->lock();
  $menus->deleteurl('/doc/');
  $menus->unlock();
  
  litepublisher::$classes->delete('tcodedocfilter');
  litepublisher::$classes->delete('tcodedocmenu');
  litepublisher::$classes->unlock();
  
  $filter = tcontentfilter::i();
  $filter->unbind($self);
  
  $manager = tdbmanager ::i();
  $manager->deletetable($self->table);
  
  
  $merger = tlocalmerger::i();
  $merger->deleteplugin(tplugins::getname(__file__));
}