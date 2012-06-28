<?php

function update535() {
if (litepublisher::$classes->exists('tregservices')) {
//litepublisher::$urlmap->delete('/odnoklassniki-oauth2callback.php');
  litepublisher::$classes->add('todnoklassnikiservice', 'odnoklassniki.service.php', 'regservices');

  $rs = tregservices::i();
$rs->lock();
$rs->data['dirname'] = 'regservices';
$rs->add(todnoklassnikiservice::i());
$rs->unlock();

    $man = tdbmanager::i();
//$row=$man->fetchnum($man->query("show create table `$man->regservices`"));
//dumpstr($row[1]);
$man->alter('regservices', 'drop key service');
$man->addenum('regservices', 'service', todnoklassnikiservice::i()->name);
$man->alter('regservices', 'add KEY (`service`, `uid`)');
}
}