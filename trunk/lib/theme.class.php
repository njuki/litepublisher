<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public $name;
  public $tmlfile;
  public $parsing;
  private $themeprops;
  
  /*
  public $menu;
  public $content;
  public $sitebars;
  public $admin;
  */
  
  public static function instance() {
    $result = getinstance(__class__);
    if ($result->name == '') {
      $template = ttemplate::instance();
      $result->loaddata($template->theme, 'index');
    }
    return $result;
  }
  
  public static function getinstance($name, $tmlfile) {
    if (isset(self::$instances[$name][$tmlfile])) {
      return self::$instances[$name][$tmlfile];
    }
    
    $result = isset(litepublisher::$classes->instances[__class__]) ? litepublisher::$classes->newinstance(__class__) : getinstance(__class__);
    $result->loaddata($name, $tmlfile);
    return $result;
  }
  
  protected function create() {
    parent::create();
    $this->themeprops = new tthemeprops($this->data);
    $this->name = '';
    $this->tmlfile = 'index';
    $this->parsing = array();
    $this->data['theme'] = '';
    $this->data['menu'] = array();
    $this->data['content'] = array();
    $this->data['sitebars'] = array();
  }
  
  public function load() {
    if ($this->name != '') return $this->loaddata($this->name, $this->tmlfile);
  }
  
  public function loaddata($name, $tmlfile) {
    $this->name = $name;
    $this->tmlfile = $tmlfile;
    $this->basename = 'themes' . DIRECTORY_SEPARATOR . "$name.$tmlfile";
    self::$instances[$name][$tmlfile] = $this;
    $datafile = litepublisher::$paths->data . $this->getbasename() .'.php';
    if (file_exists($datafile))  return parent::load();
    
    $filename = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . "$tmlfile.tml";
    if (!@file_exists($filename)) $this->error("Theme file $filename not exists");
    $parser = tthemeparser::instance();
    $parser->parse($filename, $this);
    $this->save();
  }
  
  public function __tostring() {
    return $this->theme;
  }
  
  public function __get($name) {
    if (array_key_exists($name, $this->data) && is_array($this->data[$name])) {
      $this->themeprops->array = &$this->data[$name];
      return $this->themeprops;
    } elseif ($name == 'comment') {
      $this->themeprops->array = &$this->data['content']['post']['templatecomments']['comments']['comment'];
      return $this->themeprops;
    }
    
    return parent::__get($name);
  }
  
  public function getsitebarscount() {
    return count($this->data['sitebars']);
  }
  
  public function parsecallback($names) {
    $name = $names[1];
    $prop = $names[2];
    if ($name == 'options') {
      if (($prop == 'password') || ($prop == 'cookie')) return '';
      $var = litepublisher::$options;
    } elseif (isset(self::$vars[$name])) {
      $var =  self::$vars[$name];
    } elseif (isset($GLOBALS[$name])) {
      $var =  $GLOBALS[$name];
    } else {
      $classes = litepublisher::$classes;
      if (isset($classes->classes[$name])) {
        $var = $classes->getinstance($classes->classes[$name]);
      } else {
        $class = 't' . $name;
        if (isset($classes->items[$class])) $var = $classes->getinstance($class);
      }
    }
    
    if (!isset($var)) {
      $template = ttemplate::instance();
      $var = $template->ondemand($name);
    }
    
    if (!is_object($var)) {
      litepublisher::$options->trace("Object $name not found");
      return '';
    }
    
    try {
    return $var->{$prop};
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return '';
  }
  
  public function parse($s) {
    // important! $s can be an object of tthemeprops
    // convert to string is automatic
    $s = str_replace('$options.url', litepublisher::$options->url, $s);
    array_push($this->parsing, $s);
    
    try {
      $result = preg_replace_callback('/\$(\w*+)\.(\w*+)/', array(&$this, 'parsecallback'), $s);
    } catch (Exception $e) {
      $result = '';
      litepublisher::$options->handexception($e);
    }
    array_pop($this->parsing);
    return $result;
  }
  
  public function parsearg($s, targs $args) {
    $s = strtr ($s, $args->data);
    return $this->parse($s);
  }
  
  public function getnotfount() {
    return $this->parse($this->nocontent);
  }
  
  public function getpages($url, $page, $count) {
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $link =$this->content->navi->link;
    $suburl = rtrim($url, '/');
    $a = array();
    for ($i = 1; $i <= $count; $i++) {
      $pageurl = $i == 1 ? litepublisher::$options->url . $url : litepublisher::$options->url . "$suburl/page/$i/";
      $a[] = sprintf($i == $page ? $this->content->navi->current : $link, $pageurl, $i);
    }
    
    $result = implode($this->content->navi->divider, $a);
    $result = sprintf($this->content->navi, $result);
    return $result;
  }
  
  public function getposts(array $items, $lite) {
    if (count($items) == 0) return '';
    if (dbversion) {
      $posts = tposts::instance();
      $posts->loaditems($items);
    }
    
    $result = '';
    $tml = $lite ? $this->content->excerpts->lite->excerpt : $this->content->excerpts->excerpt;
    if (is_object($tml)) $tml = $tml->__tostring();
    foreach($items as $id) {
      self::$vars['post'] = tpost::instance($id);
      $result .= $this->parse($tml);
    }
    
    $tml = $lite ? $this->content->excerpts->lite : $this->content->excerpts;
    return sprintf($this->parse($tml), $result);
  }
  
  public function getpostswidgetcontent(array $items, $sitebar, $tml) {
    if (count($items) == 0) return '';
    $result = '';
    if ($tml == '') $tml = $this->getwidgetitem('posts', $sitebar);
    foreach ($items as $id) {
      self::$vars['post'] = tpost::instance($id);
      $result .= $this->parse($tml);
    }
    $result = str_replace("'", '"', $result);
    return sprintf($this->getwidgetitems('posts', $sitebar), $result);
  }
  
  public function getwidget($title, $content, $template, $sitebar) {
    $tml = $this->getwidgettemplate($template, $sitebar);
    return sprintf($tml, $title, $content);
  }
  
  public function getwidgettemplate($name, $sitebar) {
    $sitebars = &$this->data['sitebars'];
    if (!isset($sitebars[$sitebar][$name][0])) $name = 'widget';
    return $sitebars[$sitebar][$name][0];
  }
  
  public function  getwidgetitem($name, $index) {
    return $this->getwidgettml($index, $name, 'item');
  }
  
  public function  getwidgetitems($name, $index) {
    return $this->getwidgettml($index, $name, 'items');
  }
  
  public function  getwidgettml($index, $name, $tml) {
    $sitebar = &$this->data['sitebars'][$index];
    if (isset($sitebar[$name][$tml])) return $sitebar[$name][$tml];
    if ($tml == 'items') return $sitebar['widget']['items'];
    foreach ($this->data['sitebars'] as $widgets) {
      if (isset($widgets[$name][$tml])) return $widget[$name][$tml];
    }
    return $tml == 'item' ? '<li><a href="%1$s" title="%2$s">%2$s</a></li>' : '<ul>%s</ul>';
  }
  
}//class

class tthemeprops {
  public $array;
public function __construct(array &$array) { $this->array = &$array; }
  
  public function __get($name) {
    if (is_array($this->array[$name])) {
      $this->array = &$this->array[$name];
      return $this;
    }
    return $this->array[$name];
  }
  
  public function __set($name, $value) {
    throw new Exception("try to set $name");
    //$this->array[$name] = $value;
  }
public function __tostring() { return $this->array[0]; }
  public function __isset($name) {
    return array_key_exists($name, $this->array);
  }
}//class


?>