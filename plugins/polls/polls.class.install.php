<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
$name = basename(dirname(__file__));
litepublisher::$classes->add('tpollsfilter', 'polls.filter.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);

  $about = tplugins::getabout(tplugins::getname(__file__));
  $self->deftitle = $about['title'];
  $self->voted = $about['votedmesg'];
  $self->defitems = $about['items'];
  
  $templates = parse_ini_file(dirname(__file__) . DIRECTORY_SEPARATOR . 'templates.ini',  true);
  $self->templateitems = $templates['item'];
  $self->templates = $templates['items'];
  $theme = ttheme::i();
  $lang = tplugins::getlangabout(__file__);
  $self->templates['microformat'] = $theme->replacelang($templates['microformat']['rate'], $lang);
  $self->save();
  
  $manager = tdbmanager::i();
  $manager->createtable($self->table,
  "  id int(10) unsigned NOT NULL auto_increment,
  rate tinyint unsigned NOT NULL default '0',
  status enum('opened','closed') default 'opened',
  type enum('star', 'radio','button','link','custom') default 'star',
  hash char(22) NOT NULL,
  title text NOT NULL,
  items text NOT NULL,
  votes text NOT NULL,
  
  PRIMARY KEY  ( id),
  KEY rate (rate),
  KEY hash (hash)
  ");
  
  $manager->createtable($self->votestable,
  'id int UNSIGNED NOT NULL default 0,
  user int UNSIGNED NOT NULL default 0,
  vote int UNSIGNED NOT NULL default 0,
  PRIMARY KEY(id, user)
  ');
  
  $cron = tcron::i();
  $cron->addweekly(get_class($self), 'optimize', null);
  
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->beforefilter = $self->filter;
  $filter->unlock();
  
  litepublisher::$classes->classes['poll'] = get_class($self);
  litepublisher::$classes->save();
  
  litepublisher::$options->parsepost = true;

  $json = tjsonserver::i();
  $json->addevent('polls_sendvote', get_class($self), 'polls_sendvote');

    $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->add('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->addtext('default', 'poll',
  '$(document).ready(function() {
  if ($("*[id^=\'pollform_\']").length) { window.pollclient.init(); }
  });');
  $jsmerger->unlock();

    tcssmerger::i()->addstyle(dirname(__file__) . '/stars.min.css');

tlocalmerger::i()->add('polls', "plugins/$name/resource/" . litepublisher::$options->language . ".ini");
}

function tpollsUninstall($self) {
    tcssmerger::i()->deletestyle(dirname(__file__) . '/stars.min.css');
  tjsonserver::i()->unbind($self);

$lm = tlocalmerger::i();
unset($lm->items['polls']);
$lm->save();

  $posts = tposts::i();
  $posts->lock();
  $posts->syncmeta = false;
  $posts->unbind($self);
  $posts->unlock();
  
  litepublisher::$db->table = 'postsmeta';
  litepublisher::$db->delete("name = 'poll'");
  
  unset(litepublisher::$classes->classes['poll']);
  litepublisher::$classes->save();
  
  $cron = tcron::i();
  $cron->deleteclass(get_class($self));
  
  $filter = tcontentfilter::i();
  $filter->unbind($self);
  
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->deletetext('default', 'poll');
  $jsmerger->unlock();

litepublisher::$classes->delete('tpollsfilter');
litepublisher::$classes->delete('tpollsman');

    $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable($self->votestable);
}