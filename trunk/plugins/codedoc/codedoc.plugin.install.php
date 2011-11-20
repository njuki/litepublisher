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

  $posts = tposts::i();
  $posts->added = $self->postadded;

  litepublisher::$classes->Add('tcodedocfilter', 'codedoc.filter.class.php', basename(dirname(__file__) ));
  litepublisher::$classes->Add('tcodedocclasses', 'codedoc.classes.class.php', basename(dirname(__file__) ));

  
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
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
  tposts::unsub($self);
  
  litepublisher::$classes->delete('tcodedocfilter');
  litepublisher::$classes->delete('tcodedocclasses');

  
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