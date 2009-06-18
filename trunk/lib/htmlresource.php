<?php

class THtmlResource  {
 public $section;
 public $ini;
 private $map;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function __construct() {
  $this->ini = array();
  $this->map = array();
  $this->LoadFile('adminhtml');
  TLocal::LoadLangFile('admin');
 }
 
 public function __get($name) {
  if (isset($this->ini[$this->section][$name]))  {
   $s = $this->ini[$this->section][$name];
  } elseif (isset($this->ini['common'][$name]))  {
   $s = $this->ini['common'][$name];
  } else {
   throw new Exception("the requested $name item not found in $this->section section");
  }
  $s = $this->translate($s);
  $s = $this->CommonTranslate($s);
  return $s;
 }
 
 public function translate($s){
  if (!isset($this->map[$this->section])) {
   $this->map[$this->section] = array();
   $map = &$this->map[$this->section];
   $lang = &TLocal::$data[$this->section];
   foreach ($lang as $key => $value) {
    $map['$lang->' . trim($key)] = $value;
   }
  }
  return strtr($s, $this->map[$this->section]);
 }
 
 public function CommonTranslate($s){
  $section = 'common';
  if (!isset($this->map[$section])) {
   $this->map[$section] = array();
   $map = &$this->map[$section];
   $lang = &TLocal::$data[$section];
   foreach ($lang as $key => $value) {
    $map['$lang->' . trim($key)] = $value;
   }
  }
  return strtr($s, $this->map[$section]);
 }
 
 public function LoadFile($FileName) {
  global $paths;
  $PartFileName = $paths['languages']. $FileName;
  if (!TFiler::UnserializeFromFile($PartFileName . '.php', $v) || !is_array($v)) {
   $v = parse_ini_file($PartFileName . '.ini', true);
   TFiler::SerializeToFile($PartFileName . '.php', $v);
  }
  $this->ini = $v + $this->ini;
 }
 
}

?>