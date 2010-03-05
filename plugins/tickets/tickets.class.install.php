<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
$self->infotml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'ticket.tml');
$self->save();

  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'tickets.sql'));
  }

$posts = tposts::instance();
$posts->lock();
$posts->coclasses[] = get_class($self);
$posts->addcoclass(get_class($self));
//install tticket
$class = 'tticket';
    litepublisher::$classes->Add($class, 'ticket.class.php', basename(dirname(__file__) ));
$posts->unlock();

$linkgen = tlinkgenerator::instance();
$linkgen->post = '/[type]/[title].htm';
$linkgen->save();

//install polls if its needed
$plugins = tplugins::instance();
if (dbversion) {
if (!isset($plugins->items['polls'])) $polls->add('polls');
$polls = tpolls::instance();
$polls->finddeleted = false;
$polls->save();
}
if (!isset($plugins->items['markdown'])) $plugins->add('markdown');

$filter = tcontentfilter::instance();
$filter->phpcode =  true;
$filter->save();
}

function tticketsUninstall($self) {
die("Warning! You can lost all tickets!");
$posts->coclasses[] = get_class($self);
$posts->deletecoclass(get_class($self));
//install tticket
$class = 'tticket';
    litepublisher::$classes->delete($class);
$posts->unlock();

  if ($self->dbversion) {
    $manager = tdbmanager ::instance();
    $manager->deletetable($self->table);

$polls = tpolls::instance();
$polls->finddeleted = true;
$polls->save();
  }

$linkgen = tlinkgenerator::instance();
$linkgen->post = '/[title].htm';
$linkgen->save();


}

?>