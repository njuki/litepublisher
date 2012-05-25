<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusernewsInstall($self) {
  //if (!dbversion) die("Ticket  system only for database version");
  $filter = tcontentfilter::i();
  $filter->phpcode = true;
  $filter->save();
  
  litepublisher::$options->parsepost = false;
  litepublisher::$options->reguser = true;
  $adminoptions = tadminoptions::i();
  $adminoptions->usersenabled = true;
  
  $groups = tusergroups  ::i();
  $groups->defaults = array($groups->getidgroup('author'));
  $groups->save();
  
  $rights = tauthor_rights::i();
  $rights->lock();
  $rights->getposteditor = $self->getposteditor;
  $rights->editpost = $self->editpost;
  $rights->changeposts = $self->changeposts;
  $rights->canupload = $self->canupload;
  $rights->candeletefile = $self->candeletefile;
  $rights->unlock();
}

function tusernewsUninstall($self) {
  $rights = tauthor_rights::i();
  $rights->unbind($self);
}