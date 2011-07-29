<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminbackup2dropbox {
  
  public function getcontent() {
    $plugin = tbackup2dropbox::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $form = file_get_contents($dir . 'backup2dropbox.tml');
    $html = tadminhtml::instance();
    $args = targs::instance();
    $admin = tadminplugins::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->add($about);
    $args->add($plugin->data);
$form = $html->adminform('[text=email] [password=password]  [text=dir [checkbox=onlychanged]] [checkbox=useshell]', $args);
    return $html->parsearg($form, $args);
  }
  
  public function processform() {
    $plugin = tbackup2dropbox::instance();
    if (!isset($_POST['createnow'])) {
      extract($_POST);
      $plugin->lock();
      $plugin->email = $email;
      $plugin->password = $password;
      $plugin->dir = $dir;
      $plugin->onlychanged = isset($onlychanged);
      $plugin->useshell = isset($useshell);
      $plugin->unlock();
      return '';
    } else {
      $r = $plugin->send() ;
      if ($r === true)$r = 'Uploaded';
      return sprintf('<h2>%s</h2>', $r);
    }
  }
  
}//class