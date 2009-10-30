<?php

class TAdminLogin extends TAdminPage {
  private $logonresult;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'login';
  }
  
  public function auth() { }
  
  public function GetMenu() {
    return '';
  }
  
  public function request($arg) {
    if ($arg == 'out') {
      if (!parent::auth()) {
        $auth = tauthdigest::instance();
$auth->logout();
      }
    }
    $result = parent::request($arg);
    if ($this->logonresult) return $this->logonresult;
  }
  
  public function getcontent() {
$args = new targs:();
    $args->login = '';
$args->password = '';
return $this->html->form($args);
  }
  
  public function processform() {
    global $options;
    if (!$options->auth($_POST['login'], $_POST['password']))  return $this->html->error();
      $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;
$cookie = md5uniq();
      $auth = tauthigest::instance();
      $auth->setcookies($cookie, $expired);
      $secure = 'false'; //true for sssl
      $this->logonresult = "<?php
      @setcookie('admin', '$cookie', $expired,  '$options->subdir/admin', false, $secure, true);
      @header('Location: $options->url/admin/');
      ?>";
  }
  
}//class

?>