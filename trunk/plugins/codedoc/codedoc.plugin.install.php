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
  litepublisher::$classes->Add('tcodedocfilter', 'codedoc.filter.class.php', $name);
  $merger = tlocalmerger::i();
$merger->lock();
  $merger->add('codedoc', "plugins/$name/resource/$language.ini");
  $merger->add('codedoc', "plugins/$name/resource/html.ini");
$merger->unlock();

  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, '
  id int unsigned NOT NULL default 0,
  class varchar(32) NOT NULL,
  parentclass varchar(32) NOT NULL,
methods text not null,
props text not null,
events text not null,

  KEY id (id),
  KEY parentclass (parentclass)
  ');

    $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->filterpost;
  $filter->seteventorder('beforecontent', $self, 0);

  $plugins = tplugins::i();
  if (!isset($plugins->items['wikiwords'])) $plugins->add('wikiwords');
  $filter->unlock();
  
  $about = tplugins::localabout(dirname(__file__));
  
  $linkgen = tlinkgenerator::i();
  $linkgen->data['codedoc'] = '/doc/[title].htm';
  $linkgen->save();
}

function tcodedocpluginUninstall($self) {
  //die("Warning! You can lost all tickets!");
  litepublisher::$classes->delete('tcodedocfilter');
  //litepublisher::$classes->delete('tcodedocclasses');

    $filter = tcontentfilter::i();
  $filter->unbind($self);
  
  $merger = tlocalmerger::i();
$merger->delete('codedoc');

  $manager = tdbmanager ::i();
  $manager->deletetable($self->table);
}