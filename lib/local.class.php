<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlocal {
  public static $self;
  public $loaded;
  public $ini;
  public $section;
  
  public static function i($section = '') {
    if (!isset(self::$self)) {
      self::$self= getinstance(__class__);
      self::$self->loadfile('default');
    }
    if ($section != '') self::$self->section = $section;
    return self::$self;
  }
  
  public static function admin($section = '') {
    $result = self::i($section);
    $result->check('admin');
    return $result;
  }
  
  public function __construct() {
    $this->ini = array();
    $this->loaded = array();
  }
  
  public static function get($section, $key) {
    //if (!isset(self::$self->ini[$section][$key])) throw new Exception($section);
    return self::i()->ini[$section][$key];
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    if (isset($this->ini['common'][$name])) return $this->ini['common'][$name];
    if (isset($this->ini['default'][$name])) return $this->ini['default'][$name];
    return '';
  }
  
  public function __isset($name) {
    return isset($this->ini[$this->section][$name]) ||
    isset($this->ini['common'][$name]) ||
    isset($this->ini['default'][$name]);
  }
  
  public function __call($name, $args) {
    return strtr ($this->__get($name), $args->data);
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) $format = self::i()->getdateformat();
    return self::i()->translate(date($format, $date), 'datetime');
  }
  
  public function getdateformat() {
    $format = litepublisher::$options->dateformat;
    return $format != ''? $format : $this->ini['datetime']['dateformat'];
  }
  
  public function translate($s, $section = 'default') {
    return strtr($s, $this->ini[$section]);
  }
  
  public function check($name) {
    if ($name == '') $name = 'default';
    if (!in_array($name, $this->loaded)) $this->loadfile($name);
  }
  
  public function loadfile($name) {
    $this->loaded[] = $name;
    $filename = self::getcachedir() . $name;
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini ;
    } else {
      $merger = tlocalmerger::i();
      $merger->parse($name);
    }
  }
  
  public static function usefile($name) {
    self::i()->check($name);
  }
  
  //backward
  public static function loadlang($name) {
    self::usefile($name);
  }
  
  public static function getcachedir() {
    return litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }
  
  public static function clearcache() {
    tfiler::delete(self::getcachedir(), false, false);
    self::i()->loaded = array();
  }
  
}//class

class tdateformater {
  public  $date;
public function __construct($date) { $this->date = $date; }
public function __get($name) { return tlocal::translate(date($name, $this->date), 'datetime'); }
}