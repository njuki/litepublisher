<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
    if ($s = tadminmenu::auth('editor')) return $s;
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