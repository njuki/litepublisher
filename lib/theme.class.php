<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttheme extends tevents {
public static $vars = array();
  private $themeprops;
  public static $name;
  //public $tml;
  /*
  public $menu;
  public $content;
  public $sitebars;
  public $admin;
  */
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->themeprops = new tthemeprops($this->data);
    if (empty(self::$name)) {
      $template = ttemplate::instance();
      self::$name = $template->theme . '.' . $template->tml;
    }
    $this->basename = 'themes' . DIRECTORY_SEPARATOR . self::$name;
    $this->data['tml'] = 'index';
    $this->data['theme'] = '';
    $this->data['menu'] = array();
    $this->data['content'] = array();
    $this->data['sitebars'] = array();
    
    /*
    $this->addmap('menu', array());
    $this->addmap('content', array());
    $this->addmap('sitebars', array());
    */
  }
  
  public function load() {
    $filename = litepublisher::$paths->data . $this->getbasename() .'.php';
    if (file_exists($filename)) {
      parent::load();
    } else {
      $template = ttemplate::instance();
      $parser = tthemeparser::instance();
      $parser->parse("$template->path$template->tml.tml", $this);
      $this->save();
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
  
  public static function parsecallback($names) {
    $name = $names[1];
if ($name == 'options') {
$var = litepublisher::$options;
} elseif (isset(self::$vars[$name])) {
  $var =  self::$vars[$name];
} elseif (isset($GLOBALS[$name])) {
  $var =  $GLOBALS[$name];
} else {
  $var =  litepublisher::$classes->$name;
}

    //if (!isset($var)) echo "$name\n";
    try {
    return $var->{$names[2]};
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return '';
  }
  
  public function parse($s) {
    // important! $s can be an object of tthemeprops
    // convert to string is automatic
    $s = str_replace('$options.url', litepublisher::$options->url, $s);
    try {
      return preg_replace_callback('/\$(\w*+)\.(\w*+)/', __class__ . '::parsecallback', $s);
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return '';
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
      $pageurl = $i == 1 ? litepublisher::$options->url . $url : "litepublisher::$options->url$suburl/page/$i/";
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
  
  public function getpostswidgetcontent(array $items, $tml) {
    $result = '';
    foreach ($items as $id) {
      self::$vars['post'] = tpost::instance($id);
      $result .= $this->parse($tml);
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function getwidget($title, $content, $template, $sitebar) {
    $tml = $this->getwidgettemplate($template, $sitebar);
    return sprintf($tml, $title, $content);
  }
  
  public function getwidgettemplate($name, $sitebar) {
    $sitebars = &$this->data['sitebars'];
    if (!isset($sitebars[$sitebar][$name])) $name = 'widget';
    return $sitebars[$sitebar][$name][0];
  }
  
  public function  getwidgetitem($name, $index) {
    $sitebar = &$this->data['sitebars'][$index];
    if (isset($sitebar[$name]['item'])) return $sitebar[$name]['item'];
    foreach ($this->data['sitebars'] as $sitebar) {
      if (isset($sitebar[$name]['item'])) return $sitebar[$name]['item'];
    }
    return '<li><a href="%1$s" title="%2$s">%2$s</a></li>';
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
  
public function __set($name, $value) { $this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
}//class


?>