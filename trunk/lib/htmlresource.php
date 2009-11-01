<?php

class targs {
public $data;

public function __construct($thisthis = null) {
global $options;
 $this->data = array(
'$options->url' => $options->url,
'{$options->q}' => $options->q,
'$options->files' => $options->files
);
if (isset($thisthis)) $this->data['$this' => $thisthis;
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

class THtmlResource  {
  public $section;
  public $ini;
  private $map;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->ini = array();
    $this->loadfile('adminhtml');
    tlocal::loadlang('admin');
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
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

if ($args == null) $args = new targs();
$theme = ttheme::instance();
$s = preg_replace('/\[area:(\w*+)\]/i', $theme->admin['area'],  $s);
/*
реализовать парсинг через callback
    $s = htmlspecialchars($s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace("'", '&#39;', $s);

*/
    $s = strtr ($s, $args->data);    
return $theme->parse($s);
  }

  public function load($FileName) {
    global $paths;
    $PartFileName = $paths['languages']. $FileName;
    if (!tfiler::unserialize($PartFileName . '.php', $v) || !is_array($v)) {
      $v = parse_ini_file($PartFileName . '.ini', true);
      tfiler::serialize($PartFileName . '.php', $v);
    }
    $this->ini = $v + $this->ini;
  }
  
  public function loadini($filename) {
    if( $v = parse_ini_file($filename, true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
}

?>