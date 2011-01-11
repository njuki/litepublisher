<?php

function update413() {
if (!dbversion) return;

$man = tdbmanager::instance();
$man->alter('posts', "add `class` enum('tpost') default 'tpost' after id");
if (isset(litepublisher::$classes->items['tpostclasses'])) {
$db= litepublisher::$db;
$postclasses = tpostclasses::instance();
foreach ($postclasses->classes as $id => $classname) {
if ($classname == 'tpost') continue;
$manager->addenum('posts', 'class', $classname);
$db->query("update $db->posts set class = '$classname' where id in
(select id from $db->postclasses where idclass = $id)");
}

litepublisher::$classes->delete('tpostclasses');
tstorage::savemodified();
}
}