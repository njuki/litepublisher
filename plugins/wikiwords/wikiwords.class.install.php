<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function twikiwordsInstall($self) {
if ($self->dbversion) {
  $manager = tdbmanager::instance();
  $manager->createtable($self->table,
  "  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned NOT NULL default '0',
  `post` int(10) unsigned NOT NULL default '0',
  `word` text NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `post` (`post`)
  ");
  
  $cron = tcron::instance();
  $cron->addweekly(get_class($self), 'optimize', null);
}  

  $filter = tcontentfilter::instance();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->beforefilter = $self->filter;
  $filter->unlock();
  

  litepublisher::$classes->classes['wikiwords'] = get_class($self);
  litepublisher::$classes->save();
  
  litepublisher::$options->parsepost = true;
}

function twikiwordsUninstall($self) {
  unset(litepublisher::$classes->classes['wikiword']);
  litepublisher::$classes->save();
  
  $filter = tcontentfilter::instance();
  $filter->unsubscribeclass($self);
  
if ($self->dbversion) {  
  $manager = tdbmanager::instance();
  $manager->deletetable($self->table);

  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
}
}
 
function twikiwordsOptimize($self) {
$items = $self->db->idselect('post = 0');
if (count($items) == 0) return;
    $db = litepublisher::$db;
    $posts = tposts::instance();
    $db->table = $posts->table;
$deleted = array();
foreach ($items as $id) {
    $deleted = array();
    foreach ($signs as $item) {
      if (!$db->findid("locate('\$wikiwords.word_$id', filtered) > 0")) $deleted[] = $id;
      sleep(2);
    }

      $items = sprintf('(%s)', implode(',', $deleted));
      $self->db->delete("id in $items");
}

?>
?>