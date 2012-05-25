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
  $editor = $self->add('editor', $lang->editor, '/admin/posts/');
  $author = $self->add('author', $lang->author, '/admin/posts/');
  $moder = $self->add('moderator', $lang->moderator, '/admin/comments/');
  $commentator = $self->add('commentator', $lang->commentator, '/admin/comments/');
  
  $self->items[$author]['parents'] = array($editor);
  $self->items[$commentator]['parents'] = array($moderator, $author);
  
  $self->unlock();
}