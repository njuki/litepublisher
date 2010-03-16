<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmailtemplate extends titems {
  public $name;
  
  public static function instance($name = '') {
    $result = getinstance(__class__);
    $result->name = $name;
    return $result;
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'template.mail';
    $this->dbversion = false;
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
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : targs::instance();
    return $this->getpart($name, $args);
  }
  
  public function getpart($part, targs $args) {
    tlocal::loadlang('admin');
    tlocal::instance($this->name);
    $theme = ttheme::instance();
    return $theme->parsearg($this->gettml($this->name, $part), $args);
  }
  
  public function gettml($name, $part) {
    if (isset($this->items[$name])) return $this->items[$name][$part];
    $html = THtmlResource::instance();
    $html->section = $name;
    return $html->$part;
  }
  
}//class
?>