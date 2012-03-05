<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tajaxcommentformpluginInstall($self) {
  litepublisher::$options->autocmtform = false;
  litepublisher::$urlmap->addget('/ajaxcommentform.htm', get_class($self));
  
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->add('comments', '/plugins/' . basename(dirname(__file__)) . '/ajaxcommentform.min.js');
  $jsmerger->addtext('comments', 'ajaxform', tajaxcommentformpluginGetjs());
  $jsmerger->unlock();
}

function tajaxcommentformpluginUninstall($self) {
  litepublisher::$options->autocmtform = true;
  turlmap::unsub($self);
  
  $jsmerger = tjsmerger::i();
  $jsmerger->lock();
  $jsmerger->deletefile('comments', '/plugins/' . basename(dirname(__file__)) . '/ajaxcommentform.min.js');
  $jsmerger->deletetext('comments', 'ajaxform');
  $jsmerger->unlock();
}

function tajaxcommentformpluginGetjs() {
  $name = basename(dirname(__file__));
  $lang = tlocal::admin('comments');
  $ls = array(
  'error_title' => $lang->error
  );
  return sprintf('ltoptions.commentform = %s;', json_encode($ls));
}