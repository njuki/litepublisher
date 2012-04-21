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
    $self->data['idguest'] =  0;

$self->data['confirmlogged'] = false;
$self->data['confirmguest'] = true;
$self->data['confirmcomuser'] = true;
$self->data['confirmemail'] = false;
$self->save();
  }

function tcommentmanagerUninstall($self) {

}