<?php

class ttheme extends TEventClass {
//public $tml;
public $menu;
public $navi;
public $widgets;
public $comments;
public $admin;

private $deftemplates;

public static function instance() {
return getinstance(__class__);
}

protected function create() {
parent::create();
$template = ttemplate::instance();
$this->basename = 'themes' . DIRECTORY_SEPARATOR . "$template->theme-$template->tml";
$this->data['tml'] = 'index';
$this->data['main'] = '';
$this->data['sitebarscount'] = 1;
$this->data['excerpt'] = '';
$this->data['post'] = '';
$this->data['commentform'] = '';
$this->data['menucontent'] = '';

$this->addmap('navi', array());
$this->addmap('menu', array());
$this->addmap('content', array());
$this->addmap('widgets', array());$this->addmap('widgets', array());
$this->addmap('comments', array());
$this->addmap('admin', array());

$this->deftemplates = null;
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

public static function parsecallback($var) {
try {
return {$GLOBALS[$var[1]]}->{$var[2]};
    } catch (Exception $e) {
      $options->HandleException($e);
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
      $options->HandleException($e);
    }
        return '';
}

  public function parsearg($s, targs $args) {
    $s = strtr ($s, $args->data);    
return $this->parse($s);
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
  
public function getwidget($title, $content, $template, $sitebar) {
$tml = $this->getwidgettemplate($template, $sitebar);
return sprintf($tml, $title, $content);
  }
  
public function getwidgettemplate($name, $sitebar) {
if (!isset($this->widgets[$sitebar][$name]) $name = 'widget';
return $this->widgets[$sitebar][$name];
}

public function  getwidgetitem($name) {
if (isset($this->widgets[$name)) return $this->widgets[$name];
switch ($name) {
case 'post': 
return '<li><strong><a href="$post->link" rel="bookmark" title="Permalink to $post->title">$post->iconlink$post->title</a></strong><br />
    <small>$post->localdate</small></li>';

case 'tag':
return'<li><a href="%1$s" title="%2$s">%2$s</a>%3$s</li>';

}

return '<li><a href="%1$s" title="%2$s">%2$s</a></li>';
}

}//class
?>