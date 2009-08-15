<?php

class TOptions extends TEventClass {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'options';
    $this->AddEvents('Changed', 'PostsPerPageChanged', 'OnGeturl');
    unset($this->CacheEnabled);
  }
  
  public function Load() {
    parent::Load();
    if($this->PropExists('timezone'))  date_default_timezone_set($this->timezone);
    define('gmt_offset', date('Z'));
  }
  
  public function __set($name, $value) {
    if ($this->SetEvent($name, $value)) return true;
    
    if (!isset($this->Data[$name]) || ($this->Data[$name] != $value)) {
      $this->Data[$name] = $value;
      $this->Save();
      $this->FieldChanged($name, $value);
    }
    return true;
  }
  
  private function FieldChanged($name, $value) {
    if ($name == 'postsperpage') {
      $this->PostsPerPageChanged();
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    } elseif ($name == 'CacheEnabled') {
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    } else {
      $this->Changed($name, $value);
    }
  }
  
  public function Geturl() {
    global $Urlmap;
    $s = $this->OnGeturl();
    if ($s == '') $s = $this->Data['url'];
    return $s . ($Urlmap->Ispda ? '/pda' : '');
  }
  
  public function CheckLogin($login, $password) {
    return $this->password == md5("$login:$this->realm:$password");
  }
  
  public function Auth(){
    if (isset($_SERVER['PHP_AUTH_USER'])) {
      return $this->CheckLogin($_SERVER['PHP_AUTH_USER'] , $_SERVER['PHP_AUTH_PW']);
    }
    return false;
  }
  
  public function SetPassword($value) {
    $this->password = md5("$this->login:$this->realm:$value");
  }
  
  public function Getinstalled() {
    return isset($this->Data['url']);
  }
  
  /*
  public function IsAdmin() {
    if (empty($_COOKIE['userid'])) return false;
    return $this->cookie == $_COOKIE['userid'];
  }
  */
}

?>