<?php

function update488() {
$a = array(
'tadminmenus' => 'menu.admin.class.php',
'tadminmenu' => 'menu.admin.class.php',
'tauthdigest'  => 'authdigest.class.php',
'tadminhtml' => 'htmlresource.class.php',
'tautoform' => 'htmlresource.class.php',
'ttablecolumns' => 'htmlresource.class.php',
'tuitabs' => 'htmlresource.class.php',
'tposteditor' => 'admin.posteditor.class.php',
'tajaxposteditor' => 'admin.posteditor.ajax.class.php',
'tusergroups' => 'users.groups.class.php',
'tusers' => 'users.class.php'
);
foreach ($a as $class => $filename) {
litepublisher::$classes->items[$class] = array('kernel.admin.php', '', $filename);
}
litepublisher::$classes->items['titem'] = array('kernel.php', '', 'item.class.php');
litepublisher::$classes->save();
}