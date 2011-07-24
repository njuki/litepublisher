<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tusernewsInstall($self) {
  //if (!dbversion) die("Ticket  system only for database version");
    $about = tplugins::getabout(tplugins::getname(__file__));
$self->poll = $about['poll'];
$self->save();

  $filter = tcontentfilter::instance();
  $filter->phpcode = true;
  $filter->save();
  litepublisher::$options->parsepost = false;
  litepublisher::$options->reguser = true;
  $adminoptions = tadminoptions::instance();
  $adminoptions->usersenabled = true;
  
  $groups = tusergroups  ::instance();
  $groups->defaultgroup = 'author';
  $groups->save();
  
  $rights = tauthor_rights::instance();
  $rights->lock();
  $rights->getposteditor = $self->getposteditor;
  $rights->editpost = $self->editpost;
  $rights->changeposts = $self->changeposts;
  $rights->canupload = $self->canupload;
  $rights->candeletefile = $self->candeletefile;
  $rights->unlock();
  
  $posts = tposts::instance();
$posts->lock();
  $posts->syncmeta = true;
$posts->deleted = $self->postdeleted;
  $posts->unlock();

  //install polls if its needed
  $plugins = tplugins::instance();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
 $polls = tpolls::instance();
  $polls->garbage = false;
}

function tusernewsUninstall($self) {
  $rights = tauthor_rights::instance();
  $rights->unsubscribeclass($self);

  $posts = tposts::instance();
$posts->lock();
  $posts->syncmeta = false;
$posts->unsubscribeclass($self);
  $posts->unlock();
}