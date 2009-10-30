<?php

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
  
  public static function instance($section = '') {
    $result = getinstance(__class__);
    if ($section != '') $result->section = $section;
    return $result;
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) $format = self::getdateformat();
    return self::translate(strftime ($format, $date), 'datetime');
  }
  
  public static function getddateformat() {
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
    if (!isset(self::$data)) self::$data = array();
    if (!isset(self::$files)) self::$files = array();
    if (in_array($partialname , self::$files)) return;
    self::$files[] = $partialname ;
    if (!TFiler::UnserializeFromFile($partialname . '.php', $v) || !is_array($v)) {
      $v = parse_ini_file($partialname . '.ini', true);
      TFiler::SerializeToFile($partialname . '.php', $v);
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