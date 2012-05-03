<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tjsoncommentsInstall($self) {
  $json = tjsonserver::i();
  $json->lock();
  $json->addevent('comment_delete', get_class($self), 'comment_delete');
  $json->addevent('comment_setstatus', get_class($self), 'comment_setstatus');
  $json->addevent('comment_edit', get_class($self), 'comment_edit');
  $json->addevent('comment_getraw', get_class($self), 'comment_getraw');
  $json->unlock();
}

function tjsoncommentsUninstall($self) {
  tjsonserver::i()->unbind($self);
}