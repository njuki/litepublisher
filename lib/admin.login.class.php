<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlogin extends tadminform {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function auth() {
    $auth = tauthdigest::i();
    if (litepublisher::$options->cookieenabled) {
      if ($s = $auth->checkattack()) return $s;
      if (!litepublisher::$options->authcookie()) return litepublisher::$urlmap->redir301('/admin/login/');
    }
    elseif (!$auth->Auth())  return $auth->headers();
  }
  
  private function logout() {
    $auth = tauthdigest::i();
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
      $this->formresult = $this->html->h4->cookiedisabled;
      return;
    }
    if (!isset($_POST['login']) || !isset($_POST['password'])) return;
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    if (empty($login) || empty($password)) return;
    if (!litepublisher::$options->auth($login, $password)) {
      $this->formresult = $this->html->h4->error;
      return;
    }
    
    $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;
    $cookie = md5uniq();
    $auth = tauthdigest::i();
    $auth->setcookies($cookie, $expired);
    $url = '/admin/';
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::i();
      $url = $groups->gethome(litepublisher::$options->group);
    }
    
    return litepublisher::$urlmap->redir301($url);
  }
  
  public function getcontent() {
    $args = targs::i();
    $lang = tlocal::admin('login');
    $args->formtitle = $lang->formhead;
    $args->login = !empty($_POST['login']) ? strip_tags($_POST['login']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
    $args->remember = isset($_POST['remember']);
    return $this->html->adminform('[text=login]
    [password=password]
    [checkbox=remember]',
    $args) .
    $this->html->lostpass();
  }
  
}//class

?>