<?php

class TLocal {
  public static $data;
  private static $files;
  public $section;
  
  public function __get($name) {
    if (isset(self::$data[$this->section][$name])) return self::$data[$this->section][$name];
    if (isset(self::$data['common'][$name])) return self::$data['common'][$name];
    if (isset(self::$data['default'][$name])) return self::$data['default'][$name];
    return '';
  }
  
  public static function &Instance($section = '') {
    $result = GetInstance(__class__);
    if ($section != '') $result->section = $section;
    return $result;
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) {
      $format = self::GetDateFormat();
    }
    return self::translate(date($format, $date), 'datetime');
  }
  
  public static function GetDateFormat() {
    global $Options;
    return $Options->dateformat != ''? $Options->dateformat : self::$data['datetime']['dateformat'];
  }
  
  public static function translate($s, $section = 'default') {
    return strtr($s, self::$data[$section]);
  }
  
  public static function checkload() {
    if (!isset(self::$data)) {
      self::LoadLangFile('');
    }
  }
  
  public static function LoadLangFile($FileName) {
    global $Options, $paths;
    if ($Options->language != '') {
      self::LoadFile($paths['languages']. $FileName. $Options->language);
    }
  }
  
  public static function LoadFile($PartFileName) {
    if (!isset(self::$data)) self::$data = array();
    if (!isset(self::$files)) self::$files = array();
    if (in_array($PartFileName , self::$files)) return
    self::$files[] = $PartFileName ;
    if (!TFiler::UnserializeFromFile($PartFileName . '.php', $v) || !is_array($v)) {
      $v = parse_ini_file($PartFileName . '.ini', true);
      TFiler::SerializeToFile($PartFileName . '.php', $v);
    }
    self::$data = $v + self::$data ;
  }
  
  public static function LoadIni($filename) {
    if (@file_exists($filename) && ($v = parse_ini_file($filename, true))) {
      self::$data = $v + self::$data ;
    }
  }
  
  public static function Install() {
    self::checkload();
  }
  
}//class

class TDate {
  public  $date;
  
  public function __construct($date) {
    $this->date = $date;
  }
  
  public function __get($name) {
    return TLocal::translate(date($name, $this->date), 'datetime');
  }
  
}

//init
TLocal::checkload();

?>