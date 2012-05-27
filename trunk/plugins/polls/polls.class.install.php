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

  $about = tplugins::getabout(tplugins::getname(__file__));
  $self->deftitle = $about['title'];
  $self->voted = $about['votedmesg'];
  $self->defitems = $about['items'];
  
  $templates = parse_ini_file($res . 'templates.ini',  true);
  $self->templateitems = $templates['item'];
  $self->templates = $templates['items'];
  $theme = ttheme::i();
  $lang = tplugins::getlangabout(__file__);
  $self->templates['microformat'] = $theme->replacelang($templates['microformat']['rate'], $lang);
  $self->save();


  
  $manager = tdbmanager::i();
  $manager->createtable($self->table, file_get_contents($res . 'polls.sql');
  
  $manager->createtable($self->votestable,
  'id int UNSIGNED NOT NULL default 0,
  user int UNSIGNED NOT NULL default 0,
  vote int UNSIGNED NOT NULL default 0,
  PRIMARY KEY(id, user)
  ');
  
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

tlocalmerger::i()->add('polls', "plugins/$name/resource/" . litepublisher::$options->language . ".ini");

litepublisher::$classes->add('tpollsfilter', 'polls.filter.php', $name);
litepublisher::$classes->add('tpollsman', 'polls.man.php', $name);

}

function tpollsUninstall($self) {
    tcssmerger::i()->deletestyle(dirname(__file__) . '/stars.min.css');
  tjsonserver::i()->unbind($self);

$lm = tlocalmerger::i();
unset($lm->items['polls']);
$lm->save();

  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('default', '/plugins/polls/polls.client.min.js');
  $jsmerger->deletetext('default', 'poll');
  $jsmerger->unlock();

litepublisher::$classes->delete('tpollsfilter');
litepublisher::$classes->delete('tpollsman');

    $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable($self->votestable);
}