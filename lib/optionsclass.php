<?php

class TOptions extends TEventClass {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'PostsPerPageChanged', 'OnGeturl');
    unset($this->CacheEnabled);
  }
  
  public function load() {
    parent::load();
    if($this->PropExists('timezone'))  {
      date_default_timezone_set($this->timezone);
    }
    if (!defined('gmt_offset')) define('gmt_offset', date('Z'));
  }
  
  public function __set($name, $value) {
    if ($this->setevent($name, $value)) return true;
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
      $this->FieldChanged($name, $value);
    }
    return true;
  }
  
  private function FieldChanged($name, $value) {
    if ($name == 'postsperpage') {
      $this->PostsPerPageChanged();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } elseif ($name == 'CacheEnabled') {
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } else {
      $this->changed($name, $value);
    }
  }
  
public function delete($name) {
if (array_key_exists($name, $this->data)) {
unset($this->data);
$this->save();
}
}

  public function geturl() {
    $result = $this->OnGeturl();
    if (!empty($result)) return $result;
    $result = $this->data['url'];
    if ($this->q == '&') $result .= '/index.php?url=';
    $urlmap = turlmap::instance();
    if ($urlmap->mobile) $result .= '/pda';
    return $result;
  }
  
  public function seturl($url) {
    $url = rtrim($url, '/');
    $this->lock();
    $this->data['url'] = $url;
    $this->files= $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->unlock();
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
    return isset($this->data['url']);
  }
  
  public function HandleException(&$e) {
    global $paths;
    $trace =str_replace($paths['home'], '', $e->getTraceAsString());
    $message = 'Caught exception: ' . $e->getMessage();
    $log = $message . "\n" . $trace;
    TFiler::log($log, 'exceptions.log');
    $urlmap = turlmap::instance();
    if (defined('debug') || $this->echoexception || $urlmap->admin) {
      echo str_replace("\n", "<br />\n", htmlspecialchars($log));
    } else {
      TFiler::log($log, 'exceptionsmail.log');
    }
  }
  
}//class

?>