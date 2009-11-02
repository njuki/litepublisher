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
if (preg_match_all('/\[area:(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
foreach ($m as $item) {
//сконвертировать спецсимволы для редактора
$str = &$args->data[item[1]];
    $str = htmlspecialchars($str);
    $str = str_replace('"', '&quot;', $str);
    $str = str_replace("'", '&#39;', $str);

$repl = str_replace('$name', $item[1], $theme->admin['area']);
$repl = str_replace('$content', '$'. $item[1], $repl);
$s = str_replace($item[0], $repl, $s);
}
}
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