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
    if($this->PropExists('timezone'))  {
      date_default_timezone_set($this->timezone);
      if ($this->dbversion) $this->db->exec("SET time_zone = '$this->timezone'");
    }
    if (!defined('gmt_offset')) define('gmt_offset', date('Z'));
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
    $result = $this->OnGeturl();
if (!empty($result)) return $result;
$result = $this->Data['url'];
if ($this->q == '&') $result .= '/index.php?url=';
    $urlmap = TUrlmap::Instance();
if ($Urlmap->Ispda) $result .= '/pda';
return $result;
  }
  
  public function Seturl($url) {
    $url = rtrim($url, '/');
    $this->Lock();
    $this->Data['url'] = $url;
$this->files= $url;
   $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->Unlock();
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
  
  public function HandleException(&$e) {
    global $paths;
    $trace =str_replace($paths['home'], '', $e->getTraceAsString());
    $message = 'Caught exception: ' . $e->getMessage();
    $log = $message . "\n" . $trace;
    TFiler::log($log, 'exceptions.log');
    $urlmap = TUrlmap::Instance();
    if (defined('debug') || $this->echoexception || $urlmap->IsAdminPanel) {
      echo str_replace("\n", "<br />\n", htmlspecialchars($log));
    } else {
      TFiler::log($log, 'exceptionsmail.log');
    }
  }
  
}//class

?>