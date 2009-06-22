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
$lang = &TLocal::Instance();
$lang->section = $this->section;
  return $s;
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