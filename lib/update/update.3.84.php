<?php
function update384() {
if (dbversion) {
$data = &litepublisher::$options->data;
$data['storage'] = array();
$storage = &$data['storage'];
foreach (array('posts', 'comusers', 'foaf', 'postclasses', 'externallinks', 'codedoc', 'wikiwords',
'filesitemsposts', 'pingbacks', 'users', 'urlmap', 'cron', 'comments', 'tags', 'categories') as $name) {
if (isset($data[$name])) {
$storage[$name] = $data[$name];
unset($data[$name]);
}
}
litepublisher::$options->save();
litepublisher::$options->savemodified();
}

if (class_exists('ttickets')) {
$tickets = ttickets::instance();
  $groups = tusergroups  ::instance();
$groups->onhasright = $ttickets->hasright;
}
}
?>