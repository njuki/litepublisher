<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlogin extends tadminform {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'admin.loginform';
    $this->addevents('oncontent');
    $this->data['widget'] = '';
  }
  
  public function auth() {
    if (litepublisher::$options->cookieenabled) {
      if ($s = tguard::checkattack()) return $s;
      if (!litepublisher::$options->authcookie()) return litepublisher::$urlmap->redir('/admin/login/');
    }else {
      $auth = tauthdigest::i();
      if (!$auth->Auth())  return $auth->headers();
    }
  }
  
  private function logout() {
    if (litepublisher::$options->cookieenabled) {
      if (litepublisher::$options->user) {
        litepublisher::$options->logout();
      }
      
      $subdir = litepublisher::$site->subdir . '/';
      setcookie('litepubl_user_id', '', 0, $subdir, false);
      setcookie('litepubl_user', '', 0, $subdir, false);
      setcookie('litepubl_user_flag', '', 0, $subdir, false);
      setcookie('backurl', '', 0, $subdir, false);
      return litepublisher::$urlmap->redir('/admin/login/');
    } else {
      $auth = tauthdigest::i();
      if ($auth->auth()) $auth->logout();
    }
    return litepublisher::$urlmap->redir('/admin/login/');
  }
  
  public function request($arg) {
    if ($arg == 'out')   return $this->logout($arg);
    parent::request($arg);
    $this->section = 'login';
    if (!litepublisher::$options->cookieenabled) {
      $this->formresult = $this->html->h4red->cookiedisabled;
      return;
    }
    if (!isset($_POST['email']) || !isset($_POST['password'])) return;
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    if (empty($email) || empty($password)) return;
    if (!litepublisher::$options->auth($email, $password)) {
      $this->formresult = $this->html->h4red->error;
      return;
    }
    
    $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8*3600;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);
    $url = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] :  (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));
    if (!$url) {
      $url = '/admin/';
      if (litepublisher::$options->group != 'admin') {
        $groups = tusergroups::i();
        $url = $groups->gethome(litepublisher::$options->group);
      }
    }
    
    return litepublisher::$urlmap->redir($url);
  }
  
  public function getcontent() {
    $result = $this->widget;
    if (isset($_GET['backurl'])) {
      $result = str_replace('&amp;backurl=', '&backurl=', $result);
      $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']), $result);
      //support ulogin
      $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])), $result);
    }
    
    $html = $this->html;
    $args = new targs();
    if (litepublisher::$options->usersenabled && litepublisher::$options->reguser) {
      $lang = tlocal::admin('users');
      $form = new  adminform($args);
      $form->action = litepublisher::$site->url . '/admin/reguser/';
      if (!empty($_GET['backurl'])) {
        $form->action .= '?backurl=' . urlencode($_GET['backurl']);
      }
      $form->title = $lang->regform;
      $args->email = '';
      $args->name = '';
      $form->items = '[text=email] [text=name]';
      $form->submit = 'signup';
      //fix id text-email
      $result .= str_replace('text-email', 'reg-email', $form->get());
    }
    
    $lang = tlocal::admin('login');
    $form = new adminform($args);
    $form->title = $lang->emailpass;
    $args->email = !empty($_POST['email']) ? strip_tags($_POST['email']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
    $args->remember = isset($_POST['remember']);
    $form->items = '[text=email]
    [password=password]
    [checkbox=remember]';
    
    $form->submit = 'log_in';
    $result .= $form->get();
    
    $form = new adminform($args);
    $form->title = $lang->lostpass;
    $form->action = '$site.url/admin/password/';
    $form->target = '_blank';
    $form->inline = true;
    // double "text-email" input id
    $form->items = str_replace('text-email', 'lostpass-email',
    $html->getinput('text', 'email', '', 'E-Mail'));
    $form->submit = 'sendpass';
    $result .= $form->get();
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
}//class