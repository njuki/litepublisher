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
    
    return litepublisher::$urlmap->redir301('/admin/login/');
  }
  
  public function request($arg) {
    if ($arg == 'out')   return $this->logout();
parent::request();
$this->section = 'login';
      if (!litepublisher::$options->cookieenabled) {
        $this->formresult = $this->html->h2->cookiedisabled;
        return;
      }
      
      if (empty($_POST['login']) || empty($_POST['password']) || !litepublisher::$options->auth($_POST['login'], $_POST['password'])) {
        $this->formresult = $this->html->h2->error;
        return;
      }
      
      $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;
      $cookie = md5uniq();
      $auth = tauthdigest::instance();
      $auth->setcookies($cookie, $expired);
      $options = litepublisher::$options;
      return "<?php
      @setcookie('admin', '$cookie', $expired, '$options->subdir/', false);
      @header('Location: $options->url/admin/');
      ?>";
    }
    
    //    if ($s = $this->auth()) return $s;
  }
  
  public function getcontent() {
    $args = targs::instance();
    $args->login = !empty($_POST['login']) ? strip_tags($_POST['login']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
return $this->html->form($args);
  }

}//class

?>