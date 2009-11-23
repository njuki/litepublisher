<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class ttheme extends tevents {
//public $tml;
public $excerpts;
public $post;
public $files;
public $comments;
public $navi;
public $menu;
public $widgets;
public $admin;

public static function instance() {
return getinstance(__class__);
}

protected function create() {
parent::create();
$template = ttemplate::instance();
$this->basename = 'themes' . DIRECTORY_SEPARATOR . "$template->theme.$template->tml";
$this->data['tml'] = 'index';
$this->data['main'] = '';
$this->data['sitebarscount'] = 1;
$this->data['post'] = '';
$this->data['commentform'] = '';
$this->data['menucontent'] = '';
$this->data['simplecontent'] = '%s';
$this->data['nocontent'] = '';

$this->addmap('excerpts', array());
$this->addmap('post', array());
$this->addmap('comments', array());
$this->addmap('navi', array());
$this->addmap('menu', array());
$this->addmap('widgets', array());$this->addmap('widgets', array());
$this->addmap('admin', array());
$this->addmap('files', array());
}

public function load() {
global $paths;
    $filename = $paths['data'] . $this->getbasename() .'.php';
    if (file_exists($filename)) {
parent::load();
} else {
$template = ttemplate::instance();
$parser = tthemeparser::instance();
$parser->parse("$template->path$template->tml.tml", $this);
$this->save();
}
}

public static function parsecallback($names) {
global $classes, $options;
$name = $names[1];
$var = isset($GLOBALS[$name]) ? $GLOBALS[$name] : $classes->$name;
try {
return $var->{$names[2]};
    } catch (Exception $e) {
      $options->handexception($e);
    }
return '';
}

  public function parse($s) {
    global $options, $template, $lang;
    $Template = ttemplate::instance();
    $lang = tlocal::instance();
$s = str_replace('$options->url', $options->url, $s);
    try {
return preg_replace_callback('/\$(\w*+)-\>(\w*+)/', __class__ . '::parsecallback', $s);
    } catch (Exception $e) {
      $options->handexception($e);
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
    global  $options;
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $link =$this->navi['link'];
    $suburl = rtrim($url, '/');
    $a = array();
    for ($i = 1; $i <= $count; $i++) {
      $pageurl = $i == 1 ? $options->url . $url : "$options->url$suburl/page/$i/";
      $a[] = sprintf($i == $page ? $this->navi['current'] : $link, $pageurl, $i);
    }
    
    $result = implode($this->navi['divider'], $a);
    $result = sprintf($this->navi['navi'], $result);
    return $result;
  }

public function getposts(array &$items, $lite) {
global $post;
if (count($items) == 0) return '';
if (dbversion) {
$posts = tposts::instance();
$posts->loaditems($items);
}

$result = '';
$tml = $this->excerpts[$lite ? 'lite_excerpt' : 'excerpt'];
    foreach($items as $id) {
$post = tpost::instance($id);
$result .= $this->parse($tml);
}

var_dump($this->excerpts[$lite ? 'lite' : 'normal']);
$list  = $this->parse($this->excerpts[$lite ? 'lite' : 'normal']);
return sprintf($list, $result);
}
  
public function getwidget($title, $content, $template, $sitebar) {
$tml = $this->getwidgettemplate($template, $sitebar);
return sprintf($tml, $title, $content);
  }
  
public function getwidgettemplate($name, $sitebar) {
if (!isset($this->widgets[$sitebar][$name])) $name = 'widget';
return $this->widgets[$sitebar][$name];
}

public function  getwidgetitem($name) {
if (isset($this->widgets[$name])) return $this->widgets[$name];
return '<li><a href="%1$s" title="%2$s">%2$s</a></li>';
}

}//class
?>