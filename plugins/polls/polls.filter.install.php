<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsfilterInstall($self) {
  $filter = tcontentfilter::i();
  $filter->lock();
  $filter->beforecontent = $self->beforefilter;
  $filter->beforefilter = $self->filter;
  $filter->unlock();
  
  litepublisher::$classes->classes['poll'] = get_class($self);
  litepublisher::$classes->save();
  
  litepublisher::$options->parsepost = true;
}

function tpollsfilterUninstall($self) {
  $posts = tposts::i();
  $posts->lock();
  $posts->syncmeta = false;
  $posts->unbind($self);
  $posts->unlock();

  litepublisher::$db->table = 'postsmeta';
  litepublisher::$db->delete("name = 'poll'");
  
  unset(litepublisher::$classes->classes['poll']);
  litepublisher::$classes->save();

  $filter = tcontentfilter::i();
  $filter->unbind($self);

}