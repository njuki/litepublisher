<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsInstall($self) {
$name = basename(dirname(__file__));
$res = dirname(__file__) .DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
$dir = litepublisher::$paths->data . 'polls';
@mkdir($dir, 0777);
@chmod($dir, 0777);

    $manager = tdbmanager::i();
  $manager->createtable($self->table, file_get_contents($res . 'polls.sql');
  $manager->createtable($self->users1, file_get_contents($res . 'users.sql'));
  $manager->createtable($self->users2, file_get_contents($res . 'users2.sql'));
  $manager->createtable($self->votes, file_get_contents($res . 'votes.sql'));

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

tlocalmerger::i()->addplugin($name);
$lang = tlocal::admin('polls');

litepublisher::$classes->add('tpoltypes', 'poll.types.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);
litepublisher::$classes->add('tadminpolltemplates', 'admin.poll.templates.php', $name);
litepublisher::$classes->add('tadminpolltypes', 'admin.poll.types.php', $name);

  $adminmenus = tadminmenus::i();
  $adminmenus->lock();
  
  $parent = $adminmenus->createitem($adminmenus->url2id('/admin/plugins/'),
 'polls', 'editor', 'tadminpolls');
  $adminmenus->items[$parent]['title'] = $lang->polls;
  
  $idmenu = $adminmenus->createitem($parent, 'templates', 'editor', 'tadminpolltemplates');
  $adminmenus->items[$idmenu]['title'] = $lang->templates;
  
  $idmenu = $adminmenus->createitem($parent, 'prototypes', 'editor', 'tadminpolltypes');
  $adminmenus->items[$idmenu]['title'] = $lang->prototypes;
  
  $idmenu = $adminmenus->createitem($parent, 'options', 'admin', 'tadminpolls');
  $adminmenus->items[$idmenu]['title'] = $lang->options;
  
  $adminmenus->unlock();
}

function tpollsUninstall($self) {
    tcssmerger::i()->deletestyle(dirname(__file__) . '/stars.min.css');
  tjsonserver::i()->unbind($self);
tlocalmerger::i()->deleteplugin(tplugins::getname(__file__));

  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->deletetext('default', 'poll');
  $jsmerger->unlock();

tadminmenus::i()->deletetree($adminmenus->url2id('/admin/plugins/polls/'));

litepublisher::$classes->delete('tpolltypes');
litepublisher::$classes->delete('tpollsman');
litepublisher::$classes->delete('tadminpolltemplates');
litepublisher::$classes->delete('tadminpolltypes');

    $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable($self->users1);
  $manager->deletetable($self->users2);
  $manager->deletetable($self->votes);

$dir = litepublisher::$paths->data . 'polls';
tfiler::delete($dir, true, true);
}