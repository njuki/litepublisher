<?php

function update483() {
if (litepublisher::$classes->exists('tkeywordswidget')) {
$w = tkeywordswidget::instance();
$w->links = array();
$w->save();
}

$contact = tcontactform::instance();
$contact->lock();
if (!isset($contact->data['extra'])) $contact->data['extra'] = array();
$theme = tview::instance()->theme;
  $html = tadminhtml::instance();
      $html->loadini(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.ini');
  $html->section = 'contactform';
tlocal::loadini(litepublisher::$paths->languages . litepublisher::$options->language . '.install.ini');
  $lang = tlocal::instance('contactform');
  $contact->data['subject'] = $lang->subject;
  $contact->data['success']  = $html->success();
  $contact->data['errmesg'] = $html->errmesg();
if (!strpos($contact->rawcontent, '[html]')) {
  $contact->content = str_replace('<form', '[html]<form', $contact->rawcontent);
}
$contact->unlock();
}