<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tforumInstall($self) {
  litepublisher::$options->reguser = true;
tadminoptions::i()->usersenabled = true;

$lang = tlocal::admin('forum);

$view = new tview();
$view->name = $lang->forum;
$view->themename = 'forum';
$idview = tviews::i()->addview($view);

$cats = tcategories::i();
$idcat = $cats->add(0, $lang->forum);
$cats->setvalue($idcat, 'includechilds', 1);
$cats->setvalue($idcat, 'idview', $idview);
$cats->contents->setcontent($idcat, $lang->intro);

$self->rootcat = $idcat;
$self->idview = $idview;
$self->save();

$cat = $cats->getitem($idcat);
tmenus::i()->addfake($cat['url'], $cat['title']);
}

function tforumUninstall($self) {
}