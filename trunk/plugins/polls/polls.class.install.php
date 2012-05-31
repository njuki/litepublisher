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

litepublisher::$classes->add('tpoltypes', 'poll.types.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);
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

litepublisher::$classes->delete('tpolltypes');
litepublisher::$classes->delete('tpollsman');

    $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable($self->users1);
  $manager->deletetable($self->users2);
  $manager->deletetable($self->votes);

$dir = litepublisher::$paths->data . 'polls';
tfiler::delete($dir, true, true);
}