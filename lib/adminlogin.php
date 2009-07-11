<?php

class TAdminLogin extends TAdminPage {
  private $loged;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'login';
  }
  
  public function Auth() {
  }
  
  public function GetMenu() {
    return '';
  }
  
  public function Request($arg) {
    if ($arg == 'out') {
      if (!parent::Auth()) {
        $auth = &TAuthDigest::Instance();
        $auth->cookie = '';
        $auth->cookieexpired = 0;
        $auth->Save();
      }
    }
    $result = parent::Request($arg);
    if ($this->loged) return $this->loged;
  }
  
  public function Getcontent() {
    global $Options;
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    $login = '';$password = '';
    eval('$result = "'.  $html->form . '\n";');
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function ProcessForm() {
    global $Options;
    if ($Options->CheckLogin($_POST['login'], $_POST['password'])) {
      $expired = isset($_POST['remember']) ? time() + 1210000 : 0;
      $auth = &TAuthDigest::Instance();
      $auth->cookie = md5(secret. uniqid( microtime()));
      $auth->cookieexpired = $expired == 0 ? time() + 24*3600 : $expired;
      $auth->Save();
      
      $secure = 'false'; //true for sssl
      $this->loged = "<?php
      @setcookie('admin', '$auth->cookie', $expired,  '$Options->subdir/pda/admin', false, $secure, true);
      @header('Location: $Options->url/admin/');
      ?>";
    } else {
      $html = &THtmlResource::Instance();
      $html->section = $this->basename;
      $lang = &TLocal::Instance();
      
      eval('$result = "'. $html->error . '\n";');
      return $result;
    }
  }
  
}//class

?>