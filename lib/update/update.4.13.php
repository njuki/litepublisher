<?php

function update413() {
if (!dbversion) return;

$man = tdbmanager::instance();
$man->alter('posts', "`class` enum('tpost') default 'tpost'");
if (isset(litepublisher::$classes->items['tpostclasses'])) {
$db= litepublisher::$db;
$postclasses = tpostclasses::instance();
foreach ($postclasses->classes as $id => $classname) {
if ($classname == 'tpost') continue;
$manager->alter('posts', "modify `class` enum('$classname')");
$db->query("update $db->posts set class = '$classname' where id in
(select id from $db->postclasses where idclass = $id)");
}

litepublisher::$classes->delete('tpostclasses');
tstorage::savemodified();
}
}