<?php

function update517() {
$pages = tuserpages::i();
$v = $pages->createpage;
$pages->lock();
$pages->createpage = false;
$pages->add(1, 'Admin', litepublisher::$options->email, litepublisher::$site->url . '/');
$itemurl = litepublisher::$urlmap->findurl('/');
$pages->setvalue(1, 'idurl', $itemurl['id']);
$pages->createpage = $v;
$pages->unlock();

tlocal::clearcache();
  $lang = tlocal::admin();
$js = "var lang;\nif (lang == undefined) lang = {};\n";
$merger = tjsmerger::i();
$merger->lock();
  $merger->addtext('comments', 'lang', $js . sprintf('lang.comment = %s;',  json_encode($lang->ini['comment'])));
$merger->unlock();
}