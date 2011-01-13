<?php

function update413() {
if (!dbversion) return;

$man = tdbmanager::instance();
$man->alter('posts', "add `class` enum('tpost') default 'tpost' after id");
litepublisher::$classes->add('tdboptimizer','db.optimizer.class.php');
$cron = tcron::instance();
  $cron->addnightly('tdboptimizer', 'optimize', null);
$cron->deleteclass('tdbmanager');
if (isset(litepublisher::$classes->items['tpostclasses'])) {
$db= litepublisher::$db;
$postclasses = tpostclasses::instance();
foreach ($postclasses->classes as $id => $classname) {
if ($classname == 'tpost') continue;
$man->addenum('posts', 'class', $classname);
$db->query("update $db->posts set class = '$classname' where id in
(select id from $db->postclasses where idclass = $id)");
}

litepublisher::$classes->delete('tpostclasses');
}

if (isset(litepublisher::$classes->items['tickets'])) {
$optimizer = tdboptimizer::instance();
$optimizer->lock();
$optimizer->childtables[] = 'tickets';
$optimizer->addevent('postsdeleted', 'ttickets', 'postsdeleted');
$optimizer->unlock();
    $posts = tposts::instance();
    $posts->unsubscribeclassname('ttickets');
}

tstorage::savemodified();
}