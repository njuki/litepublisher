<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusergroupsInstall($self) {
  $self->lock();
  $self->add('admin');
  $self->add('editor');
  $self->add('author');
  $self->add('moderator');
  $self->add('subscriber');
  $self->add('nobody');
  $self->unlock();
}

?>