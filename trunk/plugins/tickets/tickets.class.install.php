<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tticketsInstall($self) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
$self->infotml = file_get_contents($dir . 'ticket.tml');
$self->save();
$self->checkadminlang();

  if (dbversion) {
    $manager = tdbmanager ::instance();
    $manager->CreateTable($self->table, file_get_contents($dir .'ticket.sql'));
  }

litepublisher::$classes->lock();
$posts = tposts::instance();
$posts->lock();
$posts->coclasses[] = get_class($self);
$posts->addcoclass(get_class($self));
$posts->aftercontent = $self->aftercontent;
//install tticket
$class = 'tticket';
    litepublisher::$classes->Add($class, 'ticket.class.php', basename(dirname(__file__) ));
$posts->unlock();

//install polls if its needed
$plugins = tplugins::instance();
if (dbversion) {
if (!isset($plugins->items['polls'])) $plugins->add('polls');
$polls = tpolls::instance();
$polls->finddeleted = false;
$polls->save();
}
if (!isset($plugins->items['markdown'])) $plugins->add('markdown');

    litepublisher::$classes->Add('tticketeditor', 'admin.ticketeditor.class.php', basename(dirname(__file__)));
    litepublisher::$classes->Add('tadmintickets', 'admin.tickets.class.php', basename(dirname(__file__)));

        $menus = tadminmenus::instance();
          $idmenu = $menus->add(0, 'tickets', 'ticket', 'tadmintickets');
    $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['tickets'];
$idmenu = $menus->add($idmenu, 'editor', 'ticket', 'tticketeditor');
    $menus->items[$idmenu]['title'] = tlocal::$data['tickets']['editortitle'];
        $menus->unlock();
litepublisher::$classes->unlock();

$filter = tcontentfilter::instance();
$filter->phpcode =  true;
$filter->save();

$linkgen = tlinkgenerator::instance();
$linkgen->post = '/[type]/[title].htm';
$linkgen->save();
}

function tticketsUninstall($self) {
//die("Warning! You can lost all tickets!");
litepublisher::$classes->lock();
$posts = tposts::instance();
$posts->lock();
$posts->deletecoclass(get_class($self));
$posts->unsubscribeclass($self);
$class = 'tticket';
    litepublisher::$classes->delete($class);
$posts->unlock();

    litepublisher::$classes->delete('tticketeditor');
    litepublisher::$classes->delete('tadmintickets');

        $menus = tadminmenus::instance();
$menus->lock();
$menus->deleteurl('/admin/tickets/editor/');
$menus->deleteurl('/admin/tickets/');
        $menus->unlock();
litepublisher::$classes->unlock();
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