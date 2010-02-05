<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class targs {
  public $data;
  
  public static function instance() {
    global $classes;
    return $classes->newinstance(__class__);
  }
  
  public function __construct($thisthis = null) {
    global $options;
    $this->data = array(
    //'$options->url' => $options->url,
  //'{$options->q}' => $options->q,
    //'$options->files' => $options->files
    '$options.url' => $options->url,
  '{$options.q}' => $options->q,
    '$options.files' => $options->files
    );
    if (isset($thisthis)) $this->data['$this'] = $thisthis;
  }
  
public function __get($name) { return $this->data[$name]; }
  
  public function __set($name, $value) {
    if (is_bool($value)) {
      $value = $value ? 'checked="checked"' : '';
    }
    $this->data['$'.$name] = $value;
  }
  
  public function add(array $a) {
    foreach ($a as $key => $value) $this->__set($key, $value);
  }
}

class tlocal {
  public static $data;
  private static $files;
  public $section;
  
  public function __get($name) {
    if (isset(self::$data[$this->section][$name])) return self::$data[$this->section][$name];
    if (isset(self::$data['common'][$name])) return self::$data['common'][$name];
    if (isset(self::$data['default'][$name])) return self::$data['default'][$name];
    return '';
  }
  
  public function __call($name, $args) {
    return strtr ($this->__get($name), $args->data);
  }
  
  public static function instance($section = '') {
    $result = getinstance(__class__);
    if ($section != '') $result->section = $section;
    return $result;
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) $format = self::getdateformat();
    return self::translate(strftime ($format, $date), 'datetime');
  }
  
  public static function getdateformat() {
    global $options;
    return $options->dateformat != ''? $options->dateformat : self::$data['datetime']['dateformat'];
  }
  
  public static function translate($s, $section = 'default') {
    return strtr($s, self::$data[$section]);
  }
  
  public static function checkload() {
    if (!isset(self::$data)) {
      self::loadlang('');
    }
  }
  
  public static function loadlang($FileName) {
    global $options, $paths;
    if ($options->language != '') {
      self::load($paths['languages']. $FileName. $options->language);
    }
  }
  
  public static function load($partialname) {
    global $paths;
    if (!isset(self::$data)) self::$data = array();
    if (!isset(self::$files)) self::$files = array();
    if (in_array($partialname , self::$files)) return;
    self::$files[] = $partialname ;
    if (!tfiler::unserialize($partialname . '.php', $v) || !is_array($v)) {
      $v = parse_ini_file($partialname . '.ini', true);
      tfiler::serialize($partialname . '.php', $v);
      tfiler::ini2js($v + self::$data , $paths['files'] . basename($partialname) . '.js');
    }
    self::$data = $v + self::$data ;
  }
  
  public static function loadini($filename) {
    if (@file_exists($filename) && ($v = parse_ini_file($filename, true))) {
      self::$data = $v + self::$data ;
    }
  }
  
  public static function install() {
    self::checkload();
  }
  
}//class

class tdateformater {
  public  $date;
public function __construct($date) { $this->date = $date; }
public function __get($name) { return tlocal::translate(date($name, $this->date), 'datetime'); }
}

//init
tlocal::checkload();
?>