<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmailtemplate extends titems {
  public $name;
  
  public static function i($name = '') {
    $result = getinstance(__class__);
    $result->name = $name;
    return $result;
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'template.mail';
  }
  
  public function add($name, $subj, $body) {
    $this->items[$name] = array(
    'subject' => $subj,
    'body' => $body
    );
    $this->save();
    $this->added($name);
  }
  
  public function __call($name, $params) {
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : targs::i();
    return $this->getpart($name, $args);
  }
  
  public function getpart($part, targs $args) {
    tlocal::usefile('admin');
    tlocal::i($this->name);
    $theme = ttheme::i();
    return $theme->parsearg($this->gettml($this->name, $part), $args);
  }
  
  public function gettml($name, $part) {
    if (isset($this->items[$name])) return $this->items[$name][$part];
    $html = tadminhtml::i();
    $html->section = $name;
    return $html->$part;
  }
  
}//class