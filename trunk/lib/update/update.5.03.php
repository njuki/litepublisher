<?php

function update503() {
if (!litepublisher::$urlmap->finditem('/users.htm')) {
  litepublisher::$urlmap->add('/users.htm', 'tuserpages', 'url', 'get');
}

if (litepublisher::$classes->exists('tthemegenerator')) {
$about = tplugins::getabout('themegenerator');
  $views = tviews::i();
$themegen = tthemegenerator::i();
    $themegen->data['leftview'] = $views->add($about['left']);
  $view = tview::i($themegen->leftview);
  $view->themename = 'generator-left';

    $themegen->data['rightview'] = $views->add($about['right']);
  $view = tview::i($themegen->rightview);
  $view->themename = 'generator-right';
  $themegen->save();

litepublisher::$urlmap->setvalue($themegen->idurl, 'type', 'get');

$menus = tmenus::instance();
$fake = new tfakemenu();
    $fake->title = $about['left'];
$fake->url = $themegen->url . '?type=left';
    $fake->parent = $themegen->id;
$menus->add($fake);

$fake = new tfakemenu();
    $fake->title = $about['right'];
$fake->url = $themegen->url . '?type=right';
    $fake->parent = $themegen->id;
$menus->add($fake);

$menus->unlock();
}

}