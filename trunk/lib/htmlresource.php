<?php

class ttag {
public $tag;
public function __construct($tag) { $this->tag = $tag; }
public function __get($name) { 
$lang = tlocal::instance();
return "<$this->tag>{$lang->$name}</$this->tag>\n";
}

}//class

class THtmlResource  {
const tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong');
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
if (in_array($name, self::tags)) return new ttag($name);
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