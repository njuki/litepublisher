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
    //eval('$s = "'. $s . '";');
    return $s;
  }
  
  public function __call($name, $args) {
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    
    
    $s = $this->replacelang($s);
    return vsprintf($s, $args);
  }
  
  private function replacelang($s) {
    global $Options;
    $s = str_replace('$Options->url', $Options->url, $s);
  $s = str_replace('{$Options->q}', $Options->q, $s);
  if (preg_match_all('/\$lang-\>([a-zA-Z0-9_]{1,})/', $s, $m)) {
      $lang = TLocal::Instance();
      $lang->section = $this->section;
      $keys = array();
      for ($i = count($m[0]) - 1; $i >=0; $i--) {
        $key = $m[0][$i];
        if (!isset($keys[$key]))
      $keys[$key] = $lang->{$m[1][$i]};
      }
      ksort ($keys, SORT_STRING);
      $keys = array_reverse($keys, true);
      $s = str_replace(array_keys($keys), array_values($keys), $s);
    }
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
  
  public function LoadIni($filename) {
    if( $v = parse_ini_file($filename, true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
}

?>