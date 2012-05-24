<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusergroupsInstall($self) {
  tlocal::usefile('install');
  $lang = tlocal::i('initgroups');
  $self->lock();
  $self->add('admin', $lang->admin, '/admin/');
  $self->add('editor', $lang->editor, '/admin/posts/');
  $self->add('author', $lang->author, '/admin/posts/');
  $self->add('moderator', $lang->moderator, '/admin/comments/');
  $self->add('commentator', $lang->commentator, '/admin/comments/');
  $self->unlock();




}