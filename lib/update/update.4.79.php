<?php

function update479() {
if (dbversion) {
$man = tdbmanager::instance();
$man->alter('categories', "modify `title` text NOT NULL");
$man->alter('tags', "modify `title` text NOT NULL");
}

if (litepublisher::$classes->exists('ttidyfilter')) {
    $filter = tcontentfilter::instance();
    $filter->lock();
$filter->unsubscribeclassname('ttidyfilter');
$tidy = ttidyfilter::instance();
    $filter->onsimplefilter = $tidy->filter;
    $filter->onaftercomment = $tidy->filter;
    $filter->unlock();
}

$contact = tcontactform::instance();
$contact->lock();
if (!isset($contact->data['extra'])) $contact->data['extra'] = array();
$theme = tview::instance()->theme;
  $html = tadminhtml::instance();
      $html->loadinstall();
  $html->section = 'contactform';
  $lang = tlocal::instance('contactform');
  $contact->data['subject'] = $lang->subject;
  $contact->data['success']  = $html->success();
  $contact->data['errmesg'] = $html->errmesg();
if (!strpos($contact->rawcontent, '[html]')) {
  $contact->content = str_replace('<form', '[html]<form', $contact->rawcontent);
}
$contact->unlock();
}