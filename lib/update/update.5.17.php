<?php

function update517() {
$pages = tuserpages::i();
$v = $pages->createpage;
$pages->lock();
$pages->createpage = false;
$pages->add(1, 'Admin', litepublisher::$options->email, litepublisher::$options->url . '/');
$pages->setvalue(1, 'idurl', litepublisher::$urlmap->url2id('/'));
$pages->createpage = $v;
$pages->unlock();
}