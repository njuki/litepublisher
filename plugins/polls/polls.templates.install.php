<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpolltemplatesInstall($self) {
tcontentfilter::i()->beforefilter = $self->filter;

$self->lock();
    $self->data['types'] = array('star', 'radio', 'button', 'link', 'custom');

$self->unlock();
}

function tpolltemplatesUninstall($self) {
  $posts = tposts::i();
  $posts->lock();
  $posts->syncmeta = false;
  $posts->unbind($self);
  $posts->unlock();

  litepublisher::$db->table = 'postsmeta';
  litepublisher::$db->delete("name = 'poll'");
  
  unset(litepublisher::$classes->classes['poll']);
  litepublisher::$classes->save();

tcontentfilter::i()->unbind($self);
}