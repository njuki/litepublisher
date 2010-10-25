<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemeparser extends tevents {
  public $theme;
  private $abouts;
private $paths;
private $sitebar_index;
private $sitebar_count;

  public static function instance() {
    return getinstance(__class__);
  }
  
  public static function getwidgetnames() {
    return array('submenu', 'categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') ;
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'themeparser';
    $this->addevents('parsed');
$this->sitebar_index = 0;
$this->sitebar_count = 0;
  }

  public static function checktheme(ttheme $theme) {
    if ($about = self::get_about_wordpress_theme($theme->name)) {
      $theme->type = 'wordpress';
      return true;
    }
    return false;
  }
  
  public function parse(ttheme $theme) {
$this->checkparent($theme->name);
      $about = $this->getabout($theme->name);
switch ($about['type']) {
case 'litepublisher3':
case 'litepublisher':
$theme->type = 'litepublisher';
$ver3 = tthemeparserver3::instance();
$ver3->parse($theme);
break;

case 'litepublisher4':
$theme->type = 'litepublisher';
$this->parsetags($theme);
break;

case 'wordpress':
$theme->type = 'wordpress';
break;
}

    $this->parsed($theme);
    return true;
}

public function parsetheme(ttheme $theme) {
    $filename = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR . 'theme.txt';
    if (!@file_exists($filename))  return $this->error("The requested theme '$theme->name' not exists");

    if ($theme->name == 'default') {
$theme->template = temptytheme::getempty();
    } else {
$about = $this->getabout($theme->name);
$parentname = empty($about['parent']) ? 'default' : $about['parent'];
$parent = ttheme::instance($parentname);
$theme->template = $parent->template;
}
$s = self::getfile($filename);
$this->parsetags($theme, $s);
$this->afterparse($theme);
  }

public static function getfile($filename) {
    $s = file_get_contents($filename);
    $s = str_replace(array("\r\n", "\r", "\n\n"), "\n", $s);
$s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
$s = str_replace('$options.', '$site.', $s);
$s = strtr($s, array(
'$post.categorieslinks' => '$post.catlinks',
'$post.tagslinks' => '$post.taglinks',
'$post.subscriberss' => '$post.rsslink',
'$post.excerptcategories' => '$post.excerptcatlinks',
'$post.excerpttags' => '$post.excerpttaglinks',
));
return trim($s);
}
  
  public function getabout($name) {
    if (!isset($this->abouts)) $this->abouts = array();
    if (!isset($this->abouts[$name])) {
      $filename = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . 'about.ini';
      if (file_exists($filename) && (      $about = parse_ini_file($filename, true))) {
        if (empty($about['about']['type'])) $about['about']['type'] = 'litepublisher3';
        //join languages
        if (isset($about[litepublisher::$options->language])) {
          $about['about'] = $about[litepublisher::$options->language] + $about['about'];
        }
        $this->abouts[$name] = $about['about'];
      } elseif ($about =  twordpressthemeparser::get_about_wordpress_theme($name)){
        $about['type'] = 'wordpress';
        $this->abouts[$name] = $about;
      } else {
        $this->abouts[$name] = false;
      }
    }
    return $this->abouts[$name];
  }

public function checkparent($name) {
      $about = $this->getabout($name);
if (empty($about['parent'])) return true;
$parent = $this->getabout($about['parent']);
if (!empty($parent['parent'])) $this->error(
sprintf('Theme %s has parent %s theme which has parent %s', $name, $about['parent'], $parent['parent']));
}
  
  public function changetheme($old, $name) {
    $template = ttemplate::instance();
    if ($about = $this->getabout($old)) {
      if (!empty($about['about']['pluginclassname'])) {
        $plugins = tplugins::instance();
        $plugins->delete($old);
      }
    }
    
    $template->data['theme'] = $name;
    $template->path = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR  ;
    $template->url = litepublisher::$options->url  . '/themes/'. $template->theme;
    
    $theme = ttheme::getinstance($name, 'index');
    
    $about = $this->getabout($name);
    if (!empty($about['about']['pluginclassname'])) {
      $plugins = tplugins::instance();
      $plugins->addext($name, $about['about']['pluginclassname'], $about['about']['pluginfilename']);
    }
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function reparse() {
    $theme = ttheme::instance();
    $theme->lock();
    $this->parse($theme);
    ttheme::clearcache();
    $theme->unlock();
  }
  
//4 ver
public static function find_close($s, $a) {
$brackets = array(
'[' => ']',
'{' => '}',
'(' => ')'
);

$b = $brackets[$a];
$i = strpos($s, $b);
$sub = substr($s, 0, $i);
$opened = substr_count($sub, $a);
if ($opened == 0) return $i;

while ($opened >=  substr_count($sub, $b)) {
$i = strpos($s, $b, $i + 1);
if ($i === false) die(" The '$b' not found in\n$s");
$sub = substr($s, 0, $i);
$opened = substr_count($sub, $a);
}

return $i;
}

public function parsetags(ttheme $theme, $s) {
$s = trim($s);
$roottags = array_keys($theme->templates);
 while ($s != '') {
if (preg_match('/^(\$?\w*+(\.\w\w*+)+)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
$tag = $m[1];
$s = ltrim(substr($s, strlen($m[0])));
if (isset($m[3])) {
$i = self::find_close($s, $m[3]);
} else {
$i = strpos($s, "\n");
}

$value = trim(substr($s, 0, $i));
$s = ltrim(substr($s, $i));
$this->settag($tag, $value);
} else {
if ($i = strpos($s, "\n")) {
$s = ltrim(substr($s, $i));
} else {
$s = '';
}
}
}
return $result;
}

public function settag($parent, $s) {
if (preg_match('/file\s*=\s*(\w*+\.\w\w*+\s*)/i', $s, $m) || 
preg_match('/\@import\s*\(\s*(\w*+\.\w\w*+\s*)\)/i', $s, $m)) {
$filename = litepublisher::$paths->themes . $this->theme->name . DIRECTORY_SEPARATOR . $m[1];
if (!file_exists($filename)) $this->error("File '$filename' not found");
$s = self::getfile($filename);
}

if (strbegin($parent, '$template.')) $parent = substr($parent, strlen('$template.'));
switch ($parent) {
case 'sitebar.index':
$this->sitebar_index = (int) trim($s);
if (!isset($this->theme->templates['sitebars'][$this->sitebar_index])) $this->theme->templates['sitebars'][$this->sitebar_index] = array();
return;

case 'sitebar':
$this->sitebar_index = ++$this->sitebar_count - 1;
if (!isset($this->theme->templates['sitebars'][$this->sitebar_index])) $this->theme->templates['sitebars'][$this->sitebar_index] = array();
break;
}

 while (($s != '') && preg_match('/(\$\w*+(\.\w\w*+)+)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
if (!isset($m[3])) $this->error('The bracket not found');
$tag = $m[1];
$j = strpos($s, $m[0]);
$pre  = rtrim(substr($s, 0, $j));
$s= ltrim(substr($s, $j + strlen($m[0])));
$i = self::find_close($s, $m[3]);
$value = trim(substr($s, 0, $i));
$s = ltrim(substr($s, $i + 1));
$info = $this->getinfo($parent, $tag);
$this->settag($parent . '.' . $info['name'], $value);
$s = $pre . $info['replace'] . $s;
}

$s = trim($s);
if (strbegin($parent, 'sitebar.')) {
$data = &$this->getwidgetdata($parent);
$data = $s;
}  elseif (isset($this->paths[$parent])) {
$this->paths[$parent]['data'] = $s;
} else {
$this->error("The '$parent' tag not found");
}
}

public function getinfo($parentpath, $tag) {
$regexp = sprintf('/^%s\.(\w\w*+)$/', str_replace('.', '\.', $parentpath));
foreach ($this->paths as $path => $info) {
if  (preg_match($regexp, $path, $m)) {
if ($tag == $info['tag']) {
$info['name'] = $m1];
$info['path'] = $path;
return $info;
}
}
}

if (strbegin($parentpath, 'sitebar.')) {
$name = substr($tag, 1);
$path = $parentpath . '.' . $name;
return array(
'data' => &$this->getwidgetdata($path),
'tag' => $tag',
'replace' => $tag,
'path' => $path,
'name' => $name
);
}

$this->error("The '$tag' not found in path '$parentpath'");
}

private function &getwidgetdata($path) {
if (preg_match('/^sitebar\.(\w\w*+)(\.\w\w*+)*$/', $path, $m)) {
$widgetname = $m[1];
if ($widgetname != 'widget') || (!in_array($widgetname, self::getwidgetnames()))) $this->error("Unknown widget '$name' name");
$sitebar = &$theme->templates['sitebars'][$this->sitebar_index];
if (!isset($sitebar[$widgetname])) {
if (isset($sitebar['widget'])) {
$sitebar[$widgetname] = $sitebar['widget'];
} else {
$sitebar[$widgetname] = array(
0 => '',
'items' => '',
'item' => '',
'subitems' => ''
);
if ($widgetname == 'meta') $widget['classes'] = '';
}
}
$widget = &$sitebar[$widgetname];
if (empty($m[2])) return $widget[0];
switch ($m[2]) {
case '.items':
return $widget['items'];

case '.items.item':
return $widget['item'];

case '.items.item.subitems':
return $widget['subitems'];

case '.classes':
return $widget['classes'];
}
}
$this->error("The '$path' path is not a widget path");
}

public function afterparse($theme) {
$menu = &$theme->templates['menu'];
if (isset($menu['hover'])) {
if (!is_bool($menu['hover'])) $menu['hover'] = $menu['hover'] != 'false';
} else {
$menu['hover'] = true;

$post = &$theme->templates['content'['post'];
$excerpt = &$theme->templates['content']['excerpts']['excerpt'];
if (empty($excerpt['data'])) $excerpt['date'] = $post['date'];
foreach (array('filelist', 'catlinks', 'taglinks') as $name) {
foreach ($post[$name] as $key => $value) {
if (empty($excerpt[$name][$key])) $excerpt[$name][$key] = $value;
}
}

$sitebars = $this->theme->templates['sitebars'];
foreach ($sitebars as $i => $sitebar) {
$widget = $sitebar['widget'];
foreach (self::getwidgetnames as $widgetname) {
if (isset($sitebar[$widgetname])) {
foreach ($widget as $name => $value) {
if (empty($sitebar[$widgetname][$name])) {
$sitebars[$i][$widgetname][$name] = $value;
}
}
} else {
$sitebars[$i][$widgetname] = $widget;
}
}
}

}