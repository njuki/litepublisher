<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminboard extends tevents implements itemplate {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
  }
  
public function load() { return true; }
public function save() { return true; }
  
  public function request($id) {
    if (!litepublisher::$options->cookieenabled) return 403;
      if ($s = tguard::checkattack()) return $s;
      if (!litepublisher::$options->user) {
        return litepublisher::$urlmap->redir('/admin/login/' . litepublisher::$site->q . 'backurl=' . urlencode(litepublisher::$urlmap->url));
      }

if (!litepublisher::$options->hasgroup('editor')) {
        $url = tusergroups::i()->gethome(litepublisher::$options->group);
        return litepublisher::$urlmap->redir($url);
}

    tlocal::usefile('admin');
  }
  
  public function gethead() {
    $editor = tposteditor::i();
    return $editor->gethead();
  }
  
  public function gettitle() {
    return tlocal::get('names', 'board');
  }
  
  public function getkeywords() {
    return '';
  }
  
  public function getdescription() {
    return '';
  }
  
  public function getidview() {
    return tviews::i()->defaults['admin'];
  }
  
public function setidview($id) {}
  
  public function getcont() {
    $result = $this->logoutlink;
    $editor = tposteditor::i();
    $result .= $editor->getexternal();
    return $result;
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function gethtml($name = '') {
    $result = tadminhtml ::i();
    if ($name == '') $name = 'login';
    $result->section = $name;
    $lang = tlocal::admin($name);
    return $result;
  }
  
  public function getlang() {
    return tlocal::admin('login');
  }
  
}//class