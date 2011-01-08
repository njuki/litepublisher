<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class targs {
  public $data;
  
  public static function instance() {
    return litepublisher::$classes->newinstance(__class__);
  }
  
  public function __construct($thisthis = null) {
    $site = litepublisher::$site;
    $this->data = array(
    '$site.url' => $site->url,
  '{$site.q}' => $site->q,
    '$site.q' => $site->q,
    '$site.files' => $site->files
    );
    if (isset($thisthis)) $this->data['$this'] = $thisthis;
  }
  
  public function __get($name) {
    if (($name == 'link') && !isset($this->data['$link'])  && isset($this->data['$url'])) {
      return litepublisher::$site->url . $this->data['$url'];
    }
    return $this->data['$' . $name];
  }
  
  public function __set($name, $value) {
    if (is_bool($value)) {
      $value = $value ? 'checked="checked"' : '';
    }
    
    $this->data['$'.$name] = $value;
    $this->data["%%$name%%"] = $value;
    
    if (($name == 'url') && !isset($this->data['$link'])) {
      $this->data['$link'] = litepublisher::$site->url . $value;
      $this->data['%%link%%'] = litepublisher::$site->url . $value;
    }
  }
  
  public function add(array $a) {
    foreach ($a as $key => $value) {
      $this->__set($key, $value);
      if ($key == 'url') {
        $this->data['$link'] = litepublisher::$site->url . $value;
        $this->data['%%link%%'] = litepublisher::$site->url . $value;
      }
    }
    
    if (isset($a['title']) && !isset($a['text'])) $this->__set('text', $a['title']);
    if (isset($a['text']) && !isset($a['title']))  $this->__set('title', $a['text']);
  }
  
}//class

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
    return self::translate(date($format, $date), 'datetime');
  }
  
  public static function getdateformat() {
    $format = litepublisher::$options->dateformat;
    return $format != ''? $format : self::$data['datetime']['dateformat'];
  }
  
  public static function translate($s, $section = 'default') {
    return strtr($s, self::$data[$section]);
  }
  
  public static function checkload() {
    if (!isset(self::$data)) {
      self::$data = array();
      self::$files = array();
      if (litepublisher::$options->installed) self::loadlang('');
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
    if (tfiler::unserialize($cachefilename, $v) && is_array($v)) {
      self::$data = $v + self::$data ;
    } else {
      $v = parse_ini_file($filename . '.ini', true);
      self::$data = $v + self::$data ;
      tfiler::serialize($cachefilename, $v);
      self::ini2js($filename);
    }
  }
  
  public static function ini2js($filename) {
    $base = basename($filename);
    if (strend($base, '.admin')) {
      $js = array('comments' => self::$data['comments']);
    } else {
      $js = array('comment' => self::$data['comment']);
    }
    tfiler::ini2js($js, litepublisher::$paths->files . $base . '.js');
  }
  
  public static function loadini($filename) {
    if (in_array($filename, self::$files)) return;
    if (file_exists($filename) && ($v = parse_ini_file($filename, true))) {
      self::$data = $v + self::$data ;
      self::$files[] = $filename;
    }
  }
  
  public static function install() {
    $dir =litepublisher::$paths->data . 'languages';
    if (!is_dir($dir)) @mkdir($dir, 0777);
    @chmod($dir, 0777);
    self::checkload();
  }
  
  public static function getcachedir() {
    return litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }
  
  public static function clearcache() {
    tfiler::delete(self::getcachedir(), false, false);
    self::$files = array();
  }
  
  public static function getcachefilename($name) {
    return self::getcachedir() . $name . '.php';
  }
  
  public static function loadsection($name, $section, $dir) {
    if (!isset(self::$data[$section])) {
      $language = litepublisher::$options->language;
      if ($name != '') $name = '.' . $name;
      self::loadini($dir . $language . $name . '.ini');
      tfiler::serialize(self::getcachefilename($language . $name), self::$data);
    }
  }
  
  public static function loadinstall() {
    self::loadini(litepublisher::$paths->languages . litepublisher::$options->language . '.install.ini');
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