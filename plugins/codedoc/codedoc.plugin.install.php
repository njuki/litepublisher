<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcodedocpluginInstall($self) {
  if (!dbversion) die("Ticket  system only for database version");
$name = basename(dirname(__file__));
$language = litepublisher::$options->language;
  $merger = tlocalmerger::i();
$merger->lock();
  //$merger->addplugin(tplugins::getname(__file__));
  $merger->add('codedoc', "plugins/$name/resource/$language.ini");
  $merger->add('codedoc', "plugins/$name/resource/html.ini");
$merger->unlock();
  $merger->add('codedoc', "plugins/$name/resource/$language.ini");

  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, '
  id int unsigned NOT NULL default 0,
  parent int unsigned NOT NULL default 0,
  class varchar(32) NOT NULL,
depended text not null,
used text not null,
interfaces text not null,
  KEY id (id)
  ');
  
  $posts = tposts::i();
  $posts->added = $self->postadded;

  
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
  
  $merger = tlocalmerger::i();
  //$merger->deleteplugin(tplugins::getname(__file__));
$merger->delete('codedoc');

  $manager = tdbmanager ::i();
  $manager->deletetable($self->table);

litepublisher::$db->table = 'postsmeta';
litepublisher::$db->delete("name = 'parentclass'");
}