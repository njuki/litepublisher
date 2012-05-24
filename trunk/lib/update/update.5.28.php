<?php

function update528() {
if (isset(tstorage::$data)) {
unset(tstorage::$data['comusers']);
unset(tstorage::$data['postclasses']);

if (!litepublisher::$classes->exists('tfoaf')) unset(tstorage::$data['foaf']);
if (!litepublisher::$classes->exists('texternallinks')) unset(tstorage::$data['externallinks']);
if (isset(tstorage::$data['posts\index']) && !isset(tstorage::$data['posts/index'])) {
tstorage::$data['posts/index'] = tstorage::$data['posts\index'];
unset(tstorage::$data['posts\index']);
}

litepublisher::$options->save();
}

$groups = tusergroups::i();
foreach ($groups->items as $id => $group) {
$groups->items[$id]['parents'] = array();
}

//update() {
litepublisher::$options->data['groupnames'] = array();
$groupnames = &litepublisher::$options->data['groupnames'];
litepublisher::$options->data['parentgroups'] = array();
$parentgroups = &litepublisher::$options->data['parentgroups'];

foreach ($groups->items as $id => $group) {
$names = explode(',', $group['name']);
foreach ($names as $name) {
if ($name = trim($name)) $groupnames[$name] = $id;
}
}

$groups->items[$groupnames['author']]['parents'] = array($groupnames['editor']);
$groups->items[$groupnames['commentator']]['parents'] = array($groupnames['moderator'], $groupnames['author']);
if (isset($groupnames['ticket'])) {
$groups->items[$groupnames['author']]['parents'][] = $groupnames['ticket'];
$groups->items[$groupnames['commentator']]['parents'][] = $groupnames['ticket'];
}

foreach ($groups->items as $id => $group) {
$parentgroups[$id] = $group['parents'];
}

$groups->data['defaults'] = array($groups->getidgroup($groups->data['defaultgroup']));
unset($groups->data['defaultgroup']);
$groups->save();

litepublisher::$options->save();

litepublisher::$classes->add('tusersman', 'usersman.class.php');
  }