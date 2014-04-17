<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function emailauthInstall($self) {
  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/plugins/emailauth/resource/emailauth.popup.min.js');
  $js->add('default', '/plugins/emailauth/resource/' . litepublisher::$options->language . '.emailauth.popup.min.js');
  $js->unlock();
  
  $json = tjsonserver::i();
  $json->addevent('emailauth_auth', get_class($self), 'emailauth_auth');
}

function emailauthUninstall($self) {
  $js = tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/plugins/emailauth/resource/emailauth.popup.min.js');
  $js->deletefile('default', '/plugins/emailauth/resource/' . litepublisher::$options->language . '.emailauth.popup.min.js');
  $js->unlock();
  
  tjsonserver::i()->unbind($self);
}