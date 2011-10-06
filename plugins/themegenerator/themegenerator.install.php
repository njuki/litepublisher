<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tthemegeneratorInstall($self) {
if (!ttheme::exists('generator')) die('Theme "generator" not exists');
litepublisher::$urlmap->add('/theme-generator.htm', get_class($self), null);

$about = tplugins::getabout(tplugins::getname(__file__));
$views = tviews::i();
$self->idview = $views->add($about['name']);
  $view = tview::i($self->idview);
$view->themename = 'generator';
  }

function tthemegeneratorUninstall($self) {
turlmap::unsub($self);
$views = tviews::instance();
$views->delete($self->idview);
}