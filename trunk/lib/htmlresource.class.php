<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttag {
  public $tag;
public function __construct($tag) { $this->tag = $tag; }
  public function __get($name) {
    $lang = tlocal::instance();
  return "<$this->tag>{$lang->$name}</$this->tag>\n";
  }
  
}//class

class THtmlResource  {
  public static $tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong');
  public $section;
  public $ini;
  private $map;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->ini = array();
    $this->load('adminhtml');
    tlocal::loadlang('admin');
  }
  
  public function __get($name) {
    if (in_array($name, self::$tags)) return new ttag($name);
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    return $s;
  }
  
  public function __call($name, $params) {
    if (isset($this->ini[$this->section][$name]))  {
      $s = $this->ini[$this->section][$name];
    } elseif (isset($this->ini['common'][$name]))  {
      $s = $this->ini['common'][$name];
    } else {
      throw new Exception("the requested $name item not found in $this->section section");
    }
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : targs::instance();
    return $this->parsearg($s, $args);
  }
  
  public function parsearg($s, targs $args) {
    $theme = ttheme::instance();
    if (preg_match_all('/\[(area|edit):(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $type = $item[1];
        $name = $item[2];
        $varname = '$' . $name;
        //сконвертировать спецсимволы для редактора
        if (isset($args->data[$varname])) {
          $str = &$args->data[$varname];
          $str = htmlspecialchars($str);
          $str = str_replace('"', '&quot;', $str);
          $str = str_replace("'", '&#39;', $str);
        } else {
          $args->data[$varname] = '';
        }
        
        $tag = str_replace('$name', $name, $theme->content->admin->$type);
        $tag = str_replace('$content', $varname, $tag);
        $s = str_replace($item[0], $tag, $s);
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