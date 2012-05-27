<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
  if (!dbversion) die("Plugin can be installed only on database version");
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
  "  `id` int(10) unsigned NOT NULL auto_increment,
  `rate` tinyint unsigned NOT NULL default '0',
  `status` enum('opened','closed') default 'opened',
  `type` enum('star', 'radio','button','link','custom') default 'star',
  `hash` char(22) NOT NULL,
  `title` text NOT NULL,
  `items` text NOT NULL,
  `votes` text NOT NULL,
  
  PRIMARY KEY  (`id`),
  KEY `rate` (`rate`),
  KEY `hash` (`hash`)
  ");
  
  $manager->createtable($self->userstable,
  'id int UNSIGNED NOT NULL auto_increment,
  cookie char(22) NOT NULL,
  
  PRIMARY KEY(id),
  key cookie(cookie)
  ');
  
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
  $json->lock();
  $json->addevent('comment_delete', get_class($self), 'comment_delete');
  $json->addevent('comment_setstatus', get_class($self), 'comment_setstatus');
$json->unlock();
  

    $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->add('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->addtext('default', 'poll',
  '$(document).ready(function() {
  if ($("*[id^=\'pollform_\']").length) { window.pollclient.init(); }
  });');
  $jsmerger->unlock();

    tcssmerger::i()->addstyle(dirname(__file__) . '/stars.min.css');
}

function tpollsUninstall($self) {
    tcssmerger::i()->deletestyle(dirname(__file__) . '/stars.min.css');
  tjsonserver::i()->unbind($self);

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
  
  /*
  $template = ttemplate::i();
  $template->deletefromhead(getpollhead());
  */
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->deletetext('default', 'poll');
  $jsmerger->unlock();
  
  $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable($self->userstable);
  $manager->deletetable($self->votestable);
}


function finddeletedpols($self) {
  $signs = $self->db->selectassoc("select id, hash from $self->thistable");
  if (!$signs) return array();
  $db = litepublisher::$db;
  $posts = tposts::i();
  $db->table = $posts->rawtable;
  $deleted = array();
  foreach ($signs as $item) {
    $hash = $item['hash'];
    if (!$db->findid("locate('$hash', rawcontent) > 0")) $deleted[] = $item['id'];
    sleep(2);
  }
  
  return $deleted;
}

function tpollsDeletedeleted($self, array $deleted) {
  if (count($deleted) > 0) {
    $items = sprintf('(%s)', implode(',', $deleted));
    $self->db->delete("id in $items");
    $self->getdb($self->votestable)->delete("id in $items");
    sleep(2);
  }
}

function tpollsOptimize($self) {
  if ($self->garbage) {
    $deleted = finddeletedpols($self);
    if (count($deleted) > 0) tpollsDeletedeleted($self, $deleted);
  }
  
  $db = $self->getdb($self->userstable);
  $db->delete("id not in (select distinct user from $db->prefix$self->votestable)");
}