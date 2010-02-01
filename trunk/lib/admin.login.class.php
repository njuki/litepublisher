<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlogin extends tevents implements itemplate {
  private $formresult;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function gettitle() {
    return tlocal::$data['login']['title'];
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function auth() {
    global $options, $urlmap;
    $auth = tauthdigest::instance();
    if ($options->cookieenabled) {
      if ($s = $auth->checkattack()) return $s;
      if (!$options->authcookie()) return $urlmap->redir301('/admin/login/');
    }
    elseif (!$auth->Auth())  return $auth->headers();
  }
  
  private function logout() {
    global $options, $urlmap;
    $auth = tauthdigest::instance();
    if ($options->cookieenabled) {
      if ($options->authcookie()) $auth->logout();
    } elseif ($auth->auth()) {
      $auth->logout();
    }
    
    return $urlmap->redir301('/admin/login/');
  }
  
  public function request($arg) {
    global $options, $urlmap;
    $this->cache = false;
    if ($arg == 'out')   return $this->logout();
    tlocal::loadlang('admin');
    $this->formresult = '';
    
    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      
      if (!$options->cookieenabled) {
        $this->formresult = $this->html->h2->cookiedisabled;
        return;
      }
      
      if (empty($_POST['login']) || empty($_POST['password']) || !$options->auth($_POST['login'], $_POST['password'])) {
        $this->formresult = $this->html->h2->error;
        return;
      }
      
      $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;
      $cookie = md5uniq();
      $auth = tauthdigest::instance();
      $auth->setcookies($cookie, $expired);
      return "<?php
      @setcookie('admin', '$cookie', $expired, '$options->subdir/', false);
      @header('Location: $options->url/admin/');
      ?>";
    }
    
    //    if ($s = $this->auth()) return $s;
  }
  
  public function gettemplatecontent() {
    $result = $this->formresult;
    $args = targs::instance();
    $args->login = !empty($_POST['login']) ? strip_tags($_POST['login']) : '';
    $args->password = !empty($_POST['password']) ? strip_tags($_POST['password']) : '';
    $result .=$this->html->form($args);
    return $result;
  }
  
  public function gethtml() {
    $result = THtmlResource ::instance();
    $result->section = 'login';
    $lang = tlocal::instance('login');
    return $result;
  }
  
}//class

?>