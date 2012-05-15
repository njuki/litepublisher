<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentmanagerInstall($self) {
  $self->data['sendnotification'] =  true;
  $self->data['trustlevel'] = 2;
  $self->data['hidelink'] = false;
  $self->data['redir'] = true;
  $self->data['nofollow'] = false;
  $self->data['canedit'] =  true;
  $self->data['candelete'] =  true;

  $self->data['confirmlogged'] = false;
  $self->data['confirmguest'] = true;
  $self->data['confirmcomuser'] = true;
  $self->data['confirmemail'] = false;
  
  $self->data['comuser_subscribe'] = true;
  $self->data['idguest'] =  tusers::i()->add(array(
  'email' => '',
  'name' => tlocal::get('default', 'guest'),
  'status' => 'approved',
  'idgroups' => 'commentator'
  ));
  
  $self->data['idgroups'] = tusergroups::i()->cleangroups('admin, editor, moderator, author, commentator, ticket');
  $self->save();
  
  $comments = tcomments::i();
  $comments->lock();
  $comments->changed = $self->changed;
  $comments->added = $self->sendmail;
  $comments->unlock();
}

function tcommentmanagerUninstall($self) {
  
}