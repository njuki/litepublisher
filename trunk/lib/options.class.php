<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toptions extends tevents_storage {
  public $user;
  public $group;
  public $admincookie;
  public $gmt;
  public $errorlog;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'perpagechanged', 'onsave');
    unset($this->cache);
    $this->gmt = 0;
    $this->errorlog = '';
    $this->admincookie = false;
    $this->group = '';
  }
  
  public function afterload() {
    parent::afterload();
    date_default_timezone_set($this->timezone);
    $this->gmt = date('Z');
    if (!defined('dbversion')) {
      define('dbversion', isset($this->data['dbconfig']));
    }
  }
  
  public function savemodified() {
    $result = tstorage::savemodified();
    $this->onsave($result);
    return $result;
  }
  
  public function __set($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'perpage') {
      $this->perpagechanged();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } elseif ($name == 'cache') {
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } else {
      $this->changed($name, $value);
    }
  }
  
  public function delete($name) {
    if (array_key_exists($name, $this->data)) {
      unset($this->data[$name]);
      $this->save();
    }
  }
  
  public function authcookie() {
    if (!isset($_COOKIE['admin']))  return false;
    $cookie = basemd5((string) $_COOKIE['admin'] . litepublisher::$secret);
    if (    $cookie == basemd5( litepublisher::$secret)) return false;
    if (!empty($this->cookie ) && ($this->cookie == $cookie)) {
      if ($this->cookieexpired < time()) return false;
      $this->user = 1;
    } elseif (!$this->usersenabled)  {
      return false;
    } else {
      $users = tusers::instance();
      if ($iduser = $users->findcookie($cookie)){
        $item = $users->getitem($iduser);
        if (strtotime($item['expired']) <= time()) return false;
        $this->user = $iduser;
      } else {
        return false;
      }
    }
    
    $this->updategroup();
    return true;
  }
  
  public function auth($login, $password) {
    if ($login == '' && $password == '' && $this->cookieenabled) return $this->authcookie();
    if ($login == $this->login) {
      if ($this->data['password'] != basemd5("$login:$this->realm:$password"))  return false;
      $this->user = 1;
    } elseif(!$this->usersenabled) {
      return false;
    } else {
      $users = tusers::instance();
      if (!($this->user = $users->auth($login, $password))) return false;
    }
    $this->updategroup();
    return true;
  }
  
  public function updategroup() {
    if ($this->user == 1) {
      $this->group = 'admin';
    } else {
      $users = tusers::instance();
      $this->group = $users->getgroupname($this->user);
    }
  }

public function can_edit($idauthor) {
return ($idauthor == $this->user) || ($this->group == 'admin') || ($this->group == 'editor');
}(
  
  public function getpassword() {
    if ($this->user <= 1) return $this->data['password'];
    $users = tusers::instance();
    return $users->getvalue($this->user, 'password');
  }
  
  public function changepassword($newpassword) {
    $this->data['password'] = basemd5("$this->login:$this->realm:$newpassword");
    $this->save();
  }
  
  public function setdbpassword($password) {
    $this->data['dbconfig']['password'] = base64_encode(str_rot13 ($password));
    $this->save();
  }
  
  public function Getinstalled() {
    return isset($this->data['login']);
  }
  
  public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
      $this->data['timezone'] = $value;
      $this->save();
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function set_cookie($cookie) {
    if ($cookie != '') $cookie = basemd5((string) $cookie . litepublisher::$secret);
    $this->data['cookie'] = $cookie;
    $this->save();
  }
  
  public function getcommentsapproved() {
    return $this->DefaultCommentStatus  == 'approved';
  }
  
  public function setcommentsapproved($value) {
    $this->DefaultCommentStatus  = $value ? 'approved' : 'hold';
  }
  
  public function handexception($e) {
    /*
    echo "<pre>\n";
    $debug = debug_backtrace();
    foreach ($debug as $error) {
      echo $error['function'] ;
      echo "\n";
    }
    //array_shift($debug);
    echo "</pre>\n";
    */
    $trace =str_replace(litepublisher::$paths->home, '', $e->getTraceAsString());
    
    $message = "Caught exception:\n" . $e->getMessage();
    $log = $message . "\n" . $trace;
    $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    tfiler::log($log, 'exceptions.log');
    $urlmap = turlmap::instance();
    if (!(litepublisher::$debug || $this->echoexception || $this->admincookie || $urlmap->adminpanel)) {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
  public function trace($msg) {
    try {
      throw new Exception($msg);
    } catch (Exception $e) {
      $this->handexception($e);
    }
  }
  
  public function showerrors() {
    if (!empty($this->errorlog) && (litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      echo $this->errorlog;
    }
  }
  
}//class

?>