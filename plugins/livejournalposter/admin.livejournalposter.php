<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlivejournalposter {
  
  public function getcontent() {
    $plugin = tlivejournalposter::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    if ($plugin->template == '') $plugin->template = file_get_contents($dir. 'livejournalposter.tml');
    $form = file_get_contents($dir . 'form.tml');
    $html = THtmlResource::instance();
    $args = targs::instance();
    $admin = tadminplugins::instance();
    $about = $admin->abouts[$_GET['plugin']];
    $args->add($about);
    $args->add($plugin->data);
    $args->publicchecked = 'public' == $plugin->privacy;
    $args->privatechecked = 'private' == $plugin->privacy;
    $args->friendschecked = 'friends' == $plugin->privacy;
    return $html->parsearg($form, $args);
  }
  
  public function processform() {
    extract($_POST);
    $plugin = tlivejournalposter::instance();
    $plugin->lock();
    $plugin->host = $host;
    $plugin->login = $login;
    $plugin->password = $password;
    $plugin->community = $community;
    $plugin->privacy = $privacy;
    $plugin->template = $template;
    $plugin->unlock();
    return '';
  }
  
}
?>