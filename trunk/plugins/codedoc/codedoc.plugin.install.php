<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcodedocpluginInstall($self) {
 if (!dbversion) die("Ticket  system only for database version");
 $manager = tdbmanager ::instance();
  $manager->CreateTable($self->table, '
  id int unsigned NOT NULL default 0,
  parent int unsigned NOT NULL default 0,
  class varchar(32) NOT NULL,
  KEY id (id)
  ');

  $posts = tposts::instance();
$posts->lock();
  $posts->deleted = $self->postdeleted;
$posts->added = $self->postadded;
$posts->unlock();

  litepublisher::$classes->lock();  
  litepublisher::$classes->Add('tcodedocfilter', 'codedoc.filter.class.php', basename(dirname(__file__) ));
  $filter = tcontentfilter::instance();
$filter->lock();
  $filter->beforecontent = $self->beforefilter;
$filter->seteventorder('beforecontent', $self, 0);
  $plugins = tplugins::instance();
  if (!isset($plugins->items['wikiwords'])) $plugins->add('wikiwords');
$filter->unlock();
  litepublisher::$classes->unlock();

  $linkgen = tlinkgenerator::instance();
  $linkgen->data['codedoc'] = '/doc/[title].htm';
  $linkgen->save();
}  

function tcodedocpluginUninstall($self) {
  //die("Warning! You can lost all tickets!");
  litepublisher::$classes->lock();
  if (litepublisher::$debug) litepublisher::$classes->delete('tpostclasses');
  tposts::unsub($self);
  
  litepublisher::$classes->delete('tcodedocfilter');
  litepublisher::$classes->unlock();
  
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);

  $manager = tdbmanager ::instance();
  $manager->deletetable($self->table);
  
 tfiler::deletemask(litepublisher::$paths->languages . '*.php');
}

?>