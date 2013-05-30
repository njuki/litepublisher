<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function uloginInstall($self) {
$self->data['nets'] = array('vkontakte', 'odnoklassniki', 'mailru', 'facebook', 'twitter', 'google', 'yandex', 'livejournal', 'openid', 'flickr', 'lastfm', 'linkedin', 'liveid', 'soundcloud', 'steam', 'vimeo', 'webmoney', 'youtube', 'foursquare', 'tumblr', 'googleplus');

    tdbmanager::i()->createtable($self->table, str_replace('$names', implode("', '", $self->data['nets']), file_get_contents(dirname(__file__) . '/ulogin.sql')));
  tusers::i()->deleted = $self->userdeleted;

    $lang = tplugins::getnamelang(basename(dirname(__file__)));

$self->panel = '<h4>' . $lang->panel_title . '</h4>
<script src="//ulogin.ru/js/ulogin.js"></script>
<div id="uLogin" data-ulogin="display=small;fields=email,first_name,last_name;optional=phone,nickname;providers=vkontakte,odnoklassniki,mailru,yandex,facebook,google,twitter;hidden=other;redirect_uri=' .
urlencode(litepublisher::$site->url . $self->url . '?') . 'backurl=;"></div>';

$self->button = '<div><button type="button" id="ulogin-comment-button"><span>' . $lang->button_title . '</span></button></div>';

$self->save();

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

$js = tjsmerger::i();
$js->lock();
$js->add('default', '/plugins/ulogin/ulogin.popup.min.js');
$js->add('default', '/plugins/ulogin/' . litepublisher::$options->language . '.ulogin.popup.min.js');
$js->unlock();
}

function uloginUninstall($self) {
  tusers::i()->unbind('tregserviceuser');
  turlmap::unsub($self);
  tdbmanager::i()->deletetable($self->table);

    $alogin = tadminlogin::i();
    $alogin ->widget = $self->deletepanel($alogin ->widget);
    $alogin->save();
    
    $areg = tadminreguser::i();
    $areg->widget = $self->deletepanel($areg->widget);
    $areg->save();

    $tc = ttemplatecomments::i();
      $tc->regaccount = $self->deletepanel($tc->regaccount);
$tc->save();

$js = tjsmerger::i();
$js->lock();
$js->deletefile('default', '/plugins/ulogin/ulogin.popup.min.js');
$js->deletefile('default', '/plugins/ulogin/' . litepublisher::$options->language . '.ulogin.popup.min.js');
$js->unlock();
}