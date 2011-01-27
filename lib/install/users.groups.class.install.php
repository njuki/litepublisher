<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusergroupsInstall($self) {
  $self->lock();
  $self->add('admin', 'admin');
  $self->add('editor', '/admin/posts/');
  $self->add('subeditor', '/admin/posts/');
  $self->add('author', '/admin/posts/');
  $self->add('moderator', '/admin/comments/');
  $self->add('subscriber', '/admin/subscribers/');
  $self->add('nobody', '/admin/');
  $self->unlock();
}

?>