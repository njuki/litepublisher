<?php

class TAdminLogin extends TAdminPage {
  private $loged;
  
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
        $auth->cookie = '';
        $auth->cookieexpired = 0;
        $auth->save();
      }
    }
    $result = parent::request($arg);
    if ($this->loged) return $this->loged;
  }
  
  public function Getcontent() {
$args = new targs:();
    $args->login = '';
$args->password = '';
return $this->html->form($args);
  }
  
  public function ProcessForm() {
    global $options;
    if (!$options->auth($_POST['login'], $_POST['password']))  return $this->html->error();
      $expired = isset($_POST['remember']) ? time() + 1210000 : 0;
      $auth = tauthigest::instance();
      $auth->cookie = md5(secret. uniqid( microtime()));
      $auth->cookieexpired = $expired == 0 ? time() + 24*3600 : $expired;
      $auth->save();
      
      $secure = 'false'; //true for sssl
      $this->loged = "<?php
      @setcookie('admin', '$auth->cookie', $expired,  '$options->subdir/pda/admin', false, $secure, true);
      @header('Location: $options->url/admin/');
      ?>";
  }
  
}//class

?>