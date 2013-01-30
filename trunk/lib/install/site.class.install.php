<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsiteInstall($self) {
  $site = $self;
  $site->lock();
  $site->subdir = getrequestdir();
  $site->fixedurl = true;
  $site->url = 'http://'. strtolower($_SERVER['HTTP_HOST'])  . $site->subdir;
  $site->files =$site->data['url'];
  $site->q = '?';
  
  $site->home = '/';
  $site->keywords = "blog";
  $site->jquery_version = '1.9.0';
  $site->jqueryui_version = '1.10.1';
  $site->author = 'Admin';
  $site->video_width =450;
  $site->video_height = 300;
  $site->unlock();
}

function getrequestdir() {
  if (isset($_GET) && (count($_GET) > 0) && ($i = strpos($_SERVER['REQUEST_URI'], '?'))) {
    $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, $i);
  }
  
  if (preg_match('/index\.php$/', $_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('index.php'));
  }
  
  if (preg_match('/install\.php$/', $_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('install.php'));
  }
  
  return rtrim($_SERVER['REQUEST_URI'], '/');
}

?>