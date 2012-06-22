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

$name = basename(dirname(__file__));
  tlocalmerger::i()->addplugin($name);

$lang = tlocal::admin('forum);

$view = new tview();
$view->name = $lang->forum;
$view->themename = 'forum';
$idview = tviews::i()->addview($view);

$cats = tcategories::i();
$idcat = $cats->add(0, $lang->forum);
$cats->setvalue($idcat, 'includechilds', 1);
$cats->setvalue($idcat, 'idview', $idview);
$cats->contents->setcontent($idcat, $lang->intro . 
sprintf(' <a href="%s/admin/forum/editor/">%s</a>', litepublisher::$site->url, tlocal::get('names', 'adminpanel')));

$self->rootcat = $idcat;
$self->idview = $idview;
$self->categories_changed();
$self->save();

$cat = $cats->getitem($idcat);

litepublisher::$classes->add('tforumeditor', 'admin.forumeditor.class.php', $name);
tmenus::i()->addfake($cat['url'], $cat['title']);
tjsmerger::i()->add('default', '/plugins/forum/forum.min.js');

  $linkgen = tlinkgenerator::i();
  $linkgen->data['forum'] = '/forum/[title].htm';
  $linkgen->save();

tcategories::i()->changed = $self->categories_changed;
tthemeparser::i()->parsed = $this->themeparsed;
    ttheme::clearcache();

  $adminmenus = tadminmenus::i();
$adminmenus->createitem($adminmenus->url2id('/admin/plugins/'),
  'forum', 'author', 'tforumeditor');
}

function tforumUninstall($self) {
tcategories::i()->unbind($self);
tthemeparser::i()->unbind($this);
    ttheme::clearcache();

  tlocalmerger::i()->deleteplugin(basename(dirname(__file__)));
tjsmerger::i()->deletefile('default', '/plugins/forum/forum.min.js');

  $adminmenus = tadminmenus::i();
  $adminmenus->deletetree($adminmenus->url2id('/admin/plugins/forum/'));

litepublisher::$classes->delete('tforumeditor');

  $linkgen = tlinkgenerator::i();
  unset($linkgen->data['forum']);
  $linkgen->save();
}