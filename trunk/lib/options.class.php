<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class toptions extends tevents {
public $user;
public $group;
public $gmt;
public $errorlog;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'PostsPerPageChanged');
    unset($this->cache);
$this->gmt = date('Z');
$this->errorlog = '';
  }
  
  public function load() {
    parent::load();
    if($this->propexists('timezone'))  {
      date_default_timezone_set($this->timezone);
$this->gmt = date('Z');
    }
  }
  
  public function __set($name, $value) {
    if ($this->setevent($name, $value)) return true;
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'postsperpage') {
      $this->PostsPerPageChanged();
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
unset($this->data);
$this->save();
}
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
  
  public function auth($login, $password) {
if ($login == $this->login) {
$this->user = 1;
} else {
$users = tusers::instance();
if (!($this->user = $users->loginexists($login))) return false;
}

if ($this->password != md5("$login:$this->realm:$password"))  return false;
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

public function getpassword() {
if ($this->user <= 1) return $this->data['password'];
$users = tusers::instance();
return $users->getvalue($this->user, 'password');
}
  
  public function SetPassword($value) {
    $this->password = md5("$this->login:$this->realm:$value");
  }
  
  public function Getinstalled() {
    return isset($this->data['url']);
  }

public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
$this->data['timezone'] = $value;
$this->save();
      date_default_timezone_set($this->timezone);
$this->gmt = date('Z');
    }
}
  
  public function handexception($e) {
    global $paths;
    $trace =str_replace($paths['home'], '', $e->getTraceAsString());
    $message = "Caught exception:\n" . $e->getMessage();
    $log = $message . "\n" . $trace;
    tfiler::log($log, 'exceptions.log');
    $urlmap = turlmap::instance();
    if (defined('debug') || $this->echoexception || $urlmap->admin) {
      $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    } else {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
}//class

?>