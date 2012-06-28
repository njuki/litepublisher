<?php

function update535() {
if (litepublisher::$classes->exists('tregservices')) {
  litepublisher::$classes->add('todnoklassnikiservice', 'odnoklassniki.service.php', 'regservices');}
  $rs = tregservices::i();
$rs->lock();
$rs->data['dirname'] = 'regservices';
$rs->add(todnoklassnikiservice::i());
$rs->unlock();

    $man = tdbmanager::i();
$man->alter('regservices', drop  KEY (`service`, `uid`)');
$man->addenum('regservices', 'service', todnoklassnikiservice::i()->name);
$man->alter('regservices', 'add KEY (`service`, `uid`)');
}
}
