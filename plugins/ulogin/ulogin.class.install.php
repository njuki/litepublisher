<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function uloginInstall($self) {
    tdbmanager::i()->createtable($self->table, file_get_contents(dirname(__file__) . '/ulogin.sql'));
  tusers::i()->deleted = $self->userdeleted;

    $alogin = tadminlogin::i();
    $alogin ->widget = $self->addpanel($alogin ->widget, $self->panel);
    $alogin->save();
    
    $areg = tadminreguser::i();
    $areg->widget = $self->addpanel($areg->widget, $self->panel);
    $areg->save();

    $tc = ttemplatecomments::i();
      $tc->regaccount = $self->addpanel($tc->regaccount, $self->button);
$tc->save();

  litepublisher::$urlmap->addget($self->url, get_class($self));
  litepublisher::$urlmap->clearcache();
}

function uloginUninstall($self) {
  tusers::i()->unbind('tregserviceuser');
  turlmap::unsub($self);
  tdbmanager::i()->deletetable('$self->table);

    $alogin = tadminlogin::i();
    $alogin ->widget = $self->deletepanel($alogin ->widget);
    $alogin->save();
    
    $areg = tadminreguser::i();
    $areg->widget = $self->deletepanel($areg->widget);
    $areg->save();

    $tc = ttemplatecomments::i();
      $tc->regaccount = $self->deletepanel($tc->regaccount);
$tc->save();
}