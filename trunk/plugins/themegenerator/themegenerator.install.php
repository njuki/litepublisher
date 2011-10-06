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
$self->data['title'] = $about['name'];
$views = tviews::i();
$self->idview = $views->add($about['name']);
  $view = tview::i($self->idview);
$view->themename = 'generator';

$name = basename(dirname(__file__));
$merger = tlocalmerger::i();
$merger->lock();
  $merger->add('themegenerator', "plugins/$name/res/scheme.ini");
  $merger->add('themegenerator', sprintf('plugins/%s/res/%s.ini', $name, litepublisher::$options->language));
$merger->unlock();

  }

function tthemegeneratorUninstall($self) {
turlmap::unsub($self);
$views = tviews::instance();
$views->delete($self->idview);

$merger = tlocalmerger::i();
unset($merger->items['themegenerator']);
$merger->save();
}