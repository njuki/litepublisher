<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlocal {
private $self;
private $loaded;
public $ini;
  public $section;

  public static function instance($section = '') {
if (!isset(self::$self)) {
self::$self= getinstance(__class__);
self::$self->loadfile('default');
}
    if ($section != '') self::$self->section = $section;
    return self::$self;
  }

  public static function admin($section = '') {
$result = self::instance($section);
if (!isset($result->loaded['admin'])) $result->load('admin');
return $result;
  }

public function __construct() {
      $this->ini = array();
$this->loaded = array();
}

public function get($section, $key) {
return self::$self->ini[$section][$key];
}
  
    public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    if (isset($this->ini['common'][$name])) return $this->ini['common'][$name];
    if (isset($this->ini['default'][$name])) return $this->ini['default'][$name];
    return '';
  }
  
  public function __call($name, $args) {
    return strtr ($this->__get($name), $args->data);
  }
  
  public function date($date, $format = '') {
    if (empty($format)) $format = $this->getdateformat();
    return $this->translate(date($format, $date), 'datetime');
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
$merger = tlocalmerger::instance();
$merger->parse();
    }
  }
  
  public static function loadlang($name) {
    $langname = litepublisher::$options->language;
    if ($langname != '') {
      if ($name != '') $name = '.' . $name;
      self::load(litepublisher::$paths->languages . $langname . $name);
    }
  }
  
  public static function load($filename) {
    if (in_array($filename, self::$files)) return;
    self::$files[] = $filename;
    $cachefilename = self::getcachefilename(basename($filename));
    if (tfilestorage::loadvar($cachefilename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini ;
    } else {
      $v = parse_ini_file($filename . '.ini', true);
      $this->ini = $v + $this->ini ;
      tfilestorage::savevar($cachefilename, $v);
      //self::ini2js($filename);
    }
  }
  
  public static function loadini($filename) {
    if (in_array($filename, self::$files)) return;
    if (file_exists($filename) && ($v = parse_ini_file($filename, true))) {
      $this->ini = $v + $this->ini ;
      self::$files[] = $filename;
    }
  }
  
  public static function getcachedir() {
    return litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }
  
  public static function clearcache() {
    tfiler::delete(self::getcachedir(), false, false);
    self::instance()->loaded = array();
  }
  
  public static function getcachefilename($name) {
    return self::getcachedir() . $name;
  }
  
  public static function loadsection($name, $section, $dir) {
    tlocal::loadlang($name);
    if (!isset($this->ini[$section])) {
      $language = litepublisher::$options->language;
      if ($name != '') $name = '.' . $name;
      self::loadini($dir . $language . $name . '.ini');
      tfilestorage::savevar(self::getcachefilename($language . $name), $this->ini);
    }
  }
  
}//class

class tdateformater {
  public  $date;
public function __construct($date) { $this->date = $date; }
public function __get($name) { return tlocal::translate(date($name, $this->date), 'datetime'); }
}
