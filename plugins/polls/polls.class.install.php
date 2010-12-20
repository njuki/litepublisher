<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
  if (!dbversion) die("Plugin can be installed only on database version");
  $about = tplugins::localabout(dirname(__file__));
  $self->title = $about['title'];
  $self->voted = $about['voted'];
  $templates = parse_ini_file(dirname(__file__) . DIRECTORY_SEPARATOR . 'templates.ini',  true);
  $self->templateitems = $templates['item'];
  $self->templates = $templates['items'];
  $self->save();
  
  $manager = tdbmanager::instance();
  $manager->createtable($self->table,
  "  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('opened','closed') default 'opened',
  `type` enum('radio','button','link','custom') default 'radio',
  `hash` varchar(32) NOT NULL,
  `title` text NOT NULL,
  `items` text NOT NULL,
  `votes` text NOT NULL,
  
  PRIMARY KEY  (`id`),
  KEY `hash` (`hash`)
  ");
  
  $manager->createtable($self->userstable,
  'id int UNSIGNED NOT NULL auto_increment,
  cookie varchar(32) NOT NULL,
  
  PRIMARY KEY(id),
  key cookie(cookie)
  ');
  
  $manager->createtable($self->votestable,
  'id int UNSIGNED NOT NULL default 0,
  user int UNSIGNED NOT NULL default 0,
  vote int UNSIGNED NOT NULL default 0,
  PRIMARY KEY(id, user)
  ');
  
  $cron = tcron::instance();
  $cron->addweekly(get_class($self), 'optimize', null);
  
  $filter = tcontentfilter::instance();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->beforefilter = $self->filter;
  $filter->unlock();
  
  /*
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->lock();
  $xmlrpc->add('litepublisher.poll.sendvote', 'sendvote', get_class($self));
  $xmlrpc->add('litepublisher.poll.getcookie', 'getcookie', get_class($self));
  $xmlrpc->unlock();
  */
  litepublisher::$classes->classes['poll'] = get_class($self);
  litepublisher::$classes->save();
  
  litepublisher::$options->parsepost = true;
  
  litepublisher::$urlmap->addget('/ajaxpollserver.htm', get_class($self));
}

function tpollsUninstall($self) {
  turlmap::unsub($self);
  unset(litepublisher::$classes->classes['poll']);
  litepublisher::$classes->save();
  
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
  
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
  /*
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->deleteclass(get_class($self));
  */
  
  $manager = tdbmanager::instance();
  $manager->deletetable($self->table);
  $manager->deletetable($self->userstable);
  $manager->deletetable($self->votestable);
}

function finddeletedpols($self) {
  $signs = $self->db->selectassoc("select id, hash from $self->thistable");
  if (!$signs) return array();
  $db = litepublisher::$db;
  $posts = tposts::instance();
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

?>