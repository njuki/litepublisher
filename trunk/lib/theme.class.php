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
    
    if (($name == 'default') && ($tmlfile == 'default')) {
      $result = litepublisher::$classes->newinstance(__class__);
    } else {
      $result = isset(litepublisher::$classes->instances[__class__]) ? litepublisher::$classes->newinstance(__class__) : getinstance(__class__);
    }
    $result->loaddata($name, $tmlfile);
    return $result;
  }
  
  protected function create() {
    parent::create();
    $this->themeprops = new tthemeprops($this->data);
    $this->name = '';
    $this->tmlfile = 'index';
    $this->parsing = array();
    $this->data['type'] = 'litepublisher';
    $this->data['parent'] = '';
    $this->data['template'] = '';
    $this->data['title'] = '';
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
    
    $parser = tthemeparser::instance();
    if ($parser->parse($this)) {
      $this->save();
    }else {
      $this->error("Theme file $filename not exists");
    }
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
    //if ($prop == '') return "\$$name.";
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
      litepublisher::$options->trace(sprintf("Object $name and property $prop  not found in \n%s\n", $this->parsing[count($this->parsing) -1]));
      return '';
    }
    
    try {
    return $var->{$prop};
    } catch (Exception $e) {
      //var_dump($this->parsing[count($this->parsing)-1]);
      litepublisher::$options->handexception($e);
    }
    return '';
  }
  
  public function parse($s) {
    $s = str_replace('$options.url', litepublisher::$options->url, (string) $s);
    array_push($this->parsing, $s);
    try {
      $result = preg_replace_callback('/\$(\w*+)\.(\w\w*+)/', array(&$this, 'parsecallback'), $s);
$result = preg_replace_callback('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', array(&$this, 'parsecallback'), $result);
    } catch (Exception $e) {
      $result = '';
      litepublisher::$options->handexception($e);
    }
    array_pop($this->parsing);
    return $result;
  }
  
  public function parsearg($s, targs $args) {
    $s = $this->parse($s);
    return strtr ($s, $args->data);
  }
  
  public static function parsevar($name, $var, $s) {
    self::$vars[$name] = $var;
    $self = self::instance();
    return $self->parse($s);
  }
  
  public function gethtml($context) {
    self::$vars['context'] = $context;
    switch ($this->type) {
      case 'litepublisher':
      return $this->parse($this->template);
      
      case 'wordpress':
      return wordpress::getcontent();
    }
  }
  
  public function getnotfount() {
    return $this->parse($this->nocontent);
  }
  
  public function getpages($url, $page, $count) {
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $from = 1;
    $to = $count;
    $perpage = litepublisher::$options->perpage;
    if ($count > $perpage * 2) {
      //$page is midle of the bar
      $from = max(1, $page - ceil($perpage / 2));
      $to = min($count, $from + $perpage);
    }
    $items = range($from, $to);
    if ($items[0] != 1) array_unshift($items, 1);
    if ($items[count($items) -1] != $count) $items[] = $count;
    $navi =$this->content->navi;
    $pageurl = rtrim($url, '/') . '/page/';
    $args = targs::instance();
    $a = array();
    foreach ($items as $i) {
      $args->page = $i;
      $args->url = $i == 1 ? $url : $pageurl .$i . '/';
      $a[] = $this->parsearg(($i == $page ? $navi->current : $navi->link), $args);
    }
    
    return str_replace('$items', implode($navi->divider, $a), (string) $navi);
  }
  
  public function getposts(array $items, $lite) {
    if (count($items) == 0) return '';
    if (dbversion) {
      $posts = tposts::instance();
      $posts->loaditems($items);
    }
    
    $result = '';
    $tml = $lite ? (string) $this->content->excerpts->lite->excerpt : (string) $this->content->excerpts->excerpt;
    foreach($items as $id) {
      self::$vars['post'] = tpost::instance($id);
      $result .= $this->parse($tml);
    }
    
    $tml = $lite ? $this->content->excerpts->lite : $this->content->excerpts;
    return str_replace('$items', $result, $this->parse((string) $tml));
  }
  
  public function getpostswidgetcontent(array $items, $sitebar, $tml) {
    if (count($items) == 0) return '';
    $result = '';
    if ($tml == '') $tml = $this->getwidgetitem('posts', $sitebar);
    foreach ($items as $id) {
      self::$vars['post'] = tpost::instance($id);
      $result .= $this->parse($tml);
    }
    return str_replace('$items', $result, $this->getwidgetitems('posts', $sitebar));
  }
  
  public function getwidgetcontent($items, $name, $sitebar) {
    return str_replace('$items', $items, $this->getwidgetitems($name, $sitebar));
  }
  
  public function getwidget($title, $content, $template, $sitebar) {
    $tml = $this->getwidgettemplate($template, $sitebar);
    $args = targs::instance();
    $args->title = $title;
    $args->content = $content;
    return $this->parsearg($tml, $args);
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
    $sitebars = &$this->data['sitebars'];
    if (isset($sitebars[$index][$name][$tml])) return $sitebars[$index][$name][$tml];
    if ($tml == 'items'){
      if (isset($sitebars[$index])) return $sitebars[$index]['widget']['items'];
      return $sitebars[0]['widget']['items'];
    }
    
    foreach ($sitebars as $widgets) {
      if (isset($widgets[$name][$tml])) return $widgets[$name][$tml];
    }
    return $tml == 'item' ? '<li><a href="%1$s" title="%2$s">%2$s</a></li>' : '<ul>%s</ul>';
  }
  
  public function simple($content) {
    return str_replace('$content', $content, $this->content->simple);
  }
  
  public static function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
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
  
public function __set($name, $value) {$this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
  public function __isset($name) {
    return array_key_exists($name, $this->array);
  }
}//class


?>