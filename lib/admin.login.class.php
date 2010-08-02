<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlogin extends tadminform {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function auth() {
    $auth = tauthdigest::instance();
    if (litepublisher::$options->cookieenabled) {
      if ($s = $auth->checkattack()) return $s;
      if (!litepublisher::$options->authcookie()) return litepublisher::$urlmap->redir301('/admin/login/');
    }
    elseif (!$auth->Auth())  return $auth->headers();
  }
  
  private function logout() {
    $auth = tauthdigest::instance();
    if (litepublisher::$options->cookieenabled) {
      if (litepublisher::$options->authcookie()) $auth->logout();
    } elseif ($auth->auth()) {
      $auth->logout();
    }
    litepublisher::$options->savemodified();
    return litepublisher::$urlmap->redir301('/admin/login/');
  }
  
  public function request($arg) {
    if ($arg == 'out')   return $this->logout($arg);
    parent::request($arg);
    $this->section = 'login';
    if (!litepublisher::$options->cookieenabled) {
      $this->formresult = $this->html->h2->cookiedisabled;
      return;
    }
    
    if (empty($_POST['login']) || empty($_POST['password'])) return;
    if (!litepublisher::$options->auth($_POST['login'], $_POST['password'])) {
      $this->formresult = $this->html->h2->error;
      return;
    }
    
    $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;
    $cookie = md5uniq();
    $auth = tauthdigest::instance();
    $auth->setcookies($cookie, $expired);
    $url = '/admin/';
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::instance();
      $url = $groups->gethome($options->group);
    }
    
return litepublisher::$urlmap->redir301($url);
  }
  
  public function getcontent() {
    $args = targs::instance();
    $args->login = !empty($_POST['login']) ? strip_tags($_POST['login']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
    return $this->html->form($args);
  }
  
}//class

?>