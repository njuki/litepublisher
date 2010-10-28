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
$this->parsetheme($theme);
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
self::setempty($theme);
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
$s = strtr($s, array(
'$options.url$url' => '$link',
'$post.categorieslinks' => '$post.catlinks',
'$post.tagslinks' => '$post.taglinks',
'$post.subscriberss' => '$post.rsslink',
'$post.excerptcategories' => '$post.excerptcatlinks',
'$post.excerpttags' => '$post.excerpttaglinks',
'$options' => '$site'
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
    $template->url = litepublisher::$site->url  . '/themes/'. $template->theme;
    
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
if (substr_count($sub, $a) == 0) return $i;

while (substr_count($sub, $a) >  substr_count($sub, $b)) {
$i = strpos($s, $b, $i + 1);
if ($i === false) die(" The '$b' not found in\n$s");
$sub = substr($s, 0, $i);
}

return $i;
}

public function parsetags(ttheme $theme, $s) {
$this->theme = $theme;
$this->paths = self::getpaths($theme);
$s = trim($s);
//echo "<pre>\n";
 while ($s != '') {
if (preg_match('/^(((\$template|\$custom)?\.?)?\w*+(\.\w\w*+)*)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
$tag = $m[1];
//echo "tag $tag\n";
$s = ltrim(substr($s, strlen($m[0])));
if (isset($m[5])) {
$i = self::find_close($s, $m[5]);
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
//echo "parent=$parent\n\n";
$stop = 'som';
 while (($s != '') && preg_match('/(\$\w*+(\.\w\w*+)?)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
if (!isset($m[3])) $this->error('The bracket not found');
$tag = $m[1];
$j = strpos($s, $m[0]);
$pre  = rtrim(substr($s, 0, $j));
$s= ltrim(substr($s, $j + strlen($m[0])));
if ($tag == $stop) {
echo "\nafter trim\n";
dumpstr($pre);
echo "s=\n";
dumpstr($s);
}
$i = self::find_close($s, $m[3]);
$value = trim(substr($s, 0, $i));
$s = ltrim(substr($s, $i + 1));
if ($tag == $stop) {
echo "pre\n";
dumpstr($pre);
echo "extra value\n";
dumpstr($s);
var_dump($value);
}

$info = $this->getinfo($parent, $tag);
$this->settag($parent . '.' . $info['name'], $value);
$s = $pre . $info['replace'] . $s;
}

$s = trim($s);
if (strbegin($parent, 'sitebar.')) {
$this->setwidgetvalue($parent, $s);
}  elseif (isset($this->paths[$parent])) {
$this->paths[$parent]['data'] = $s;
} elseif (strbegin($parent, '$custom') || strbegin($parent, 'custom')) {
$this->setcustom($parent, $s);
} else {
$this->error("The '$parent' tag not found. Content \n$s");
}
}

public function getinfo($parentpath, $tag) {
$regexp = sprintf('/^%s\.(\w\w*+)$/', str_replace('.', '\.', $parentpath));
foreach ($this->paths as $path => $info) {
if  (preg_match($regexp, $path, $m)) {
if ($tag == $info['tag']) {
$info['name'] = $m[1];
$info['path'] = $path;
return $info;
}
}
}

$name = substr($tag, 1);
if (strbegin($parentpath, 'sitebar')) {
$path = $parentpath . '.' . $name;
return array(
'data' => null,
'tag' => $tag,
'replace' => $tag,
'path' => $path,
'name' => $name
);
}

if (strbegin($parentpath, '$custom') || strbegin($parentpath, 'custom')) {
return array(
'data' => null,
'tag' => $tag,
'replace' => '',
'path' => $parentpath . '.' . $name,
'name' => $name
);
}
$this->error("The '$tag' not found in path '$parentpath'");
}

private function setwidgetvalue($path, $value) {
if (!preg_match('/^sitebar\.(\w\w*+)(\.\w\w*+)*$/', $path, $m)) $this->error("The '$path' is not a widget path");
$widgetname = $m[1];
if (($widgetname != 'widget') && (!in_array($widgetname, self::getwidgetnames()))) $this->error("Unknown widget '$widgetname' name");
$sitebar = &$this->theme->templates['sitebars'][$this->sitebar_index];
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
if (empty($m[2])) {
$widget[0] = $value;
} else {
switch ($m[2]) {
case '.items':
$widget['items'] = $value;
return;

case '.items.item':
case '.item':
$widget['item'] = $value;
return;

case '.items.item.subitems':
case '.item.subitems':
case '.subitems':
$widget['subitems'] = $value;
return;

case '.classes':
$widget['classes'] = $value;
return;

default:
$this->error("Unknown '$path' widget path");
}
}
}

public function setcustom($path, $value) {
$names = explode('.', $path);
if (count($names) < 2) return;
if (($names[0] != '$custom') && ($names[0] != 'custom')) $this->error("The '$path' path is not a custom path");
$name = $names[1];
switch (count($names)) {
case 2:
$this->theme->templates['custom'][$name] = $value;
return;

case 3: 
return;

case 4:
$tag = $names[3];
$admin = &$this->theme->templates['customadmin'];
if (!isset($admin[$name])) $admin[$name] = array();
$admin[$name][$tag] = $value;
return;
}
}

public function afterparse($theme) {
$menu = &$theme->templates['menu'];
if (isset($menu['hover'])) {
if (!is_bool($menu['hover'])) $menu['hover'] = $menu['hover'] != 'false';
} else {
$menu['hover'] = true;
}

$post = &$theme->templates['content']['post'];
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

foreach (self::getwidgetnames() as $widgetname) {
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
if (is_string($sitebars[$i]['meta']['classes'])) {
$sitebars[$i]['meta']['classes'] = self::getmetaclasses($sitebars[$i]['meta']['classes']);
}
}

}

public static function getmetaclasses($s) {
    $result = array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
    foreach (explode(',', $s) as $class) {
      if ($i = strpos($class, '=')) {
        $classname = trim(substr($class, 0, $i));
        $value = trim(substr($class, $i + 1));
        if ($value != '') $result[$classname] = sprintf('class="%s"', $value);
      }
    }
        return $result;
}

public static function setempty(ttheme $theme) {
$theme->templates = array (
  0 => '',
  'title' => '',
  'menu' => 
  array (
    'submenu' => '',
    'item' => '',
    'current' => '',
    'id' => '',
    'tag' => '',
    'hover' => true,
    0 => '',
  ),
  'content' => 
  array (
    'post' => 
    array (
      'more' => '',
      'rsslink' => '',
      'catlinks' => 
      array (
        'item' => '',
        'divider' => '',
        0 => '',
      ),
      'taglinks' => 
      array (
        'item' => '',
        'divider' => '',
        0 => '',
      ),
      'filelist' => 
      array (
        'file' => '',
        'image' => '',
        'preview' => '',
        'audio' => '',
        'video' => '',
        0 => '',
      ),
      'prevnext' => 
      array (
        'prev' => '',
        'next' => '',
        0 => '',
      ),
      'templatecomments' => 
      array (
        'moderateform' => '',
        'closed' => '',
        'form' => '',
        'confirmform' => '',
        'holdcomments' => '',
        'comments' => 
        array (
          'count' => '',
          'id' => '',
          'comment' => 
          array (
            'class1' => '',
            'class2' => '',
            'moderate' => '',
            'dateformat' => '',
            0 => '',
          ),
          'commentsid' => '',
          0 => '',
        ),
        'pingbacks' => 
        array (
          'pingback' => '',
          0 => '',
        ),
      ),
      'dateformat' => '',
      0 => '',
    ),
    'excerpts' => 
    array (
      'excerpt' => 
      array (
        0 => '',
        'morelink' => '',
        'date' => '',
        'catlinks' => 
        array (
          'item' => '',
          'divider' => '',
          0 => '',
        ),
        'taglinks' => 
        array (
          'item' => '',
          'divider' => '',
          0 => '',
        ),
        'filelist' => 
        array (
          'file' => '',
          'image' => '',
          'preview' => '',
          'audio' => '',
          'video' => '',
          0 => '',
        )
      ),
      'lite' => 
      array (
        'excerpt' => '',
        0 => '',
      ),
      0 => '',
    ),
    'navi' => 
    array (
      'prev' => '',
      'next' => '',
      'link' => '',
      'current' => '',
      'divider' => '',
      0 => '',
    ),
    'admin' => 
    array (
      'area' => '',
      'editor' => '',
      'text' => '',
      'checkbox' => '',
      'combo' => '',
      'hidden' => '',
      'form' => '',
    ),
    'menu' => '',
    'simple' => '',
    'notfound' => '',
  ),
  'sitebars' => 
  array (
    0 => 
    array (
      'widget' => 
      array (
        'item' => '',
        'items' => '',
        'subitems' => '',
        0 => '',
      ),
    ),
  ),
'custom' => array(),
'customadmin' => array()
);
}

public static function getpaths(ttheme $theme) {
$data = &$theme->templates;
$content = &$theme->templates['content'];
$post = &$theme->templates['content']['post'];
$excerpt = &$theme->templates['content']['excerpts']['excerpt'];

return array(
'' => array(
'data' => &$data[0],
'tag' => '',
'replace' => ''
),

'$template' => array(
'data' => &$data[0],
'tag' => '',
'replace' => ''
),

'title' => array(
'data' => &$data['title'],
'tag' => '$template.title',
'replace' => '$template.title'
),

'menu' => array(
'data' => &$data['menu'][0],
'tag' => '$template.menu',
'replace' => '$template.menu'
),

'menu.hover' => array(
'data' => &$data['menu']['hover'],
'tag' => '$hover',
'replace' => ''
),

'menu.id' => array(
'data' => &$data['menu']['id'],
'tag' => '$id',
'replace' => ''
),

'menu.tag' => array(
'data' => &$data['menu']['tag'],
'tag' => '$tag',
'replace' => ''
),

'menu.item' => array(
'data' => &$data['menu']['item'],
'tag' => '$item',
'replace' => '$item'
),

'menu.current' => array(
'data' => &$data['menu']['current'],
'tag' => '$current',
'replace' => ''
),

'menu.item.submenu' => array(
'data' => &$data['menu']['submenu'],
'tag' => '$submenu',
'replace' => '$submenu'
),

'content' => array(
'data' => null,
'tag' => '$template.content',
'replace' => '$template.content'
),

'content.simple' => array(
'data' => &$content['simple'],
'tag' => '$simple',
'replace' => ''
),

'content.notfound' => array(
'data' => &$content['notfound'],
'tag' => '$notfound',
'replace' => ''
),

'content.menu' => array(
'data' => &$content['menu'],
'tag' => '$menu',
'replace' => ''
),

'content.post' => array(
'data' => &$post[0],
'tag' => '$post',
'replace' => ''
),

'content.post.more' => array(
'data' => &$post['more'],
'tag' => '$post.more',
'replace' => ''
),

'content.post.rsslink' => array(
'data' => &$post['rsslink'],
'tag' => '$post.rsslink',
'replace' => '$post.rsslink'
),

'content.post.date' => array(
'data' => &$post['date'],
'tag' => '$post.date',
'replace' => '$post.date'
),

'content.post.filelist' => array(
'data' => &$post['filelist'][0],
'tag' => '$post.filelist',
'replace' => '$post.filelist'
),

'content.post.filelist.file' => array(
'data' => &$post['filelist']['file'],
'tag' => '$file',
'replace' => '$file'
),

'content.post.filelist.image' => array(
'data' => &$post['filelist']['image'],
'tag' => '$image',
'replace' => ''
),

'content.post.filelist.preview' => array(
'data' => &$post['filelist']['preview'],
'tag' => '$preview',
'replace' => ''
),

'content.post.filelist.audio' => array(
'data' => &$post['filelist']['audio'],
'tag' => '$audio',
'replace' => ''
),

'content.post.filelist.video' => array(
'data' => &$post['filelist']['video'],
'tag' => '$video',
'replace' => ''
),

'content.post.catlinks' => array(
'data' => &$post['catlinks'][0],
'tag' => '$post.catlinks',
'replace' => '$post.catlinks'
),

'content.post.catlinks.item' => array(
'data' => &$post['catlinks']['item'],
'tag' => '$item',
'replace' => '$items'
),

'content.post.catlinks.divider' => array(
'data' => &$post['catlinks']['divider'],
'tag' => '$divider',
'replace' => ''
),

'content.post.taglinks' => array(
'data' => &$post['taglinks'][0],
'tag' => '$post.taglinks',
'replace' => '$post.taglinks'
),

'content.post.taglinks.item' => array(
'data' => &$post['taglinks']['item'],
'tag' => '$item',
'replace' => '$items'
),

'content.post.taglinks.divider' => array(
'data' => &$post['taglinks']['divider'],
'tag' => '$divider',
'replace' => ''
),

'content.post.prevnext' => array(
'data' => &$post['prevnext'][0],
'tag' => '$post.prevnext',
'replace' => '$post.prevnext'
),

'content.post.prevnext.prev' => array(
'data' => &$post['prevnext']['prev'],
'tag' => '$prev',
'replace' => '$prev'
),

'content.post.prevnext.next' => array(
'data' => &$post['prevnext']['next'],
'tag' => '$next',
'replace' => '$next'
),

'content.post.templatecomments' => array(
'data' => null,
'tag' => '$post.templatecomments',
'replace' => '$post.templatecomments'
),

'content.post.templatecomments.closed' => array(
'data' => &$post['templatecomments']['closed'],
'tag' => '$closed',
'replace' => ''
),

'content.post.templatecomments.form' => array(
'data' => &$post['templatecomments']['form'],
'tag' => '$form',
'replace' => ''
),

'content.post.templatecomments.confirmform' => array(
'data' => &$post['templatecomments']['confirmform'],
'tag' => '$confirmform',
'replace' => ''
),

'content.post.templatecomments.moderateform' => array(
'data' => &$post['templatecomments']['moderateform'],
'tag' => '$moderateform',
'replace' => ''
),

'content.post.templatecomments.holdcomments' => array(
'data' => &$post['templatecomments']['holdcomments'],
'tag' => '$holdcomments',
'replace' => ''
),

'content.post.templatecomments.comments' => array(
'data' => &$post['templatecomments']['comments'][0],
'tag' => '$comments',
'replace' => ''
),

'content.post.templatecomments.comments.id' => array(
'data' => &$post['templatecomments']['comments']['id'],
'tag' => '$id',
'replace' => ''
),

'content.post.templatecomments.comments.idhold' => array(
'data' => &$post['templatecomments']['comments']['idhold'],
'tag' => '$idhold',
'replace' => ''
),

'content.post.templatecomments.comments.count' => array(
'data' => &$post['templatecomments']['comments']['count'],
'tag' => '$count',
'replace' => ''
),

'content.post.templatecomments.comments.comment' => array(
'data' => &$post['templatecomments']['comments']['comment'][0],
'tag' => '$comment',
'replace' => '$comment'
),

'content.post.templatecomments.comments.comment.class1' => array(
'data' => &$post['templatecomments']['comments']['comment']['class1'],
'tag' => '$class1',
'replace' => ' $class'
),

'content.post.templatecomments.comments.comment.class2' => array(
'data' => &$post['templatecomments']['comments']['comment']['class2'],
'tag' => '$class2',
'replace' => ' '
),

'content.post.templatecomments.comments.comment.date' => array(
'data' => &$post['templatecomments']['comments']['comment']['date'],
'tag' => '$comment.date',
'replace' => '$comment.date'
),

'content.post.templatecomments.comments.comment.moderate' => array(
'data' => &$post['templatecomments']['comments']['comment']['moderate'],
'tag' => '$moderate',
'replace' => '$moderate'
),

'content.post.templatecomments.pingbacks' => array(
'data' => &$post['templatecomments']['pingbacks'][0],
'tag' => '$pingbacks',
'replace' => ''
),

'content.post.templatecomments.pingbacks.pingback' => array(
'data' => &$post['templatecomments']['pingbacks']['pingback'],
'tag' => '$pingback',
'replace' => '$pingback'
),

'content.excerpts' => array(
'data' => &$content['excerpts'][0],
'tag' => '$excerpts',
'replace' => ''
),

'content.excerpts.excerpt' => array(
'data' => &$excerpt[0],
'tag' => '$excerpt',
'replace' => '$excerpt'
),

'content.excerpts.excerpt.date' => array(
'data' => &$excerpt['date'],
'tag' => '$post.excerptdate',
'replace' => '$post.excerptdate'
),

'content.excerpts.excerpt.morelink' => array(
'data' => &$excerpt['morelink'],
'tag' => '$post.morelink',
'replace' => ''
),


'content.excerpts.excerpt.filelist' => array(
'data' => &$excerpt['filelist'][0],
'tag' => '$post.excerptfilelist',
'replace' => '$post.excerptfilelist'
),

'content.excerpts.excerpt.filelist.file' => array(
'data' => &$excerpt['filelist']['file'],
'tag' => '$file',
'replace' => '$file'
),

'content.excerpts.excerpt.filelist.image' => array(
'data' => &$excerpt['filelist']['image'],
'tag' => '$image',
'replace' => ''
),

'content.excerpts.excerpt.filelist.preview' => array(
'data' => &$excerpt['filelist']['preview'],
'tag' => '$preview',
'replace' => ''
),

'content.excerpts.excerpt.filelist.audio' => array(
'data' => &$excerpt['filelist']['audio'],
'tag' => '$audio',
'replace' => ''
),

'content.excerpts.excerpt.filelist.video' => array(
'data' => &$excerpt['filelist']['video'],
'tag' => '$video',
'replace' => ''
),

'content.excerpts.excerpt.catlinks' => array(
'data' => &$excerpt['catlinks'][0],
'tag' => '$post.excerptcatlinks',
'replace' => '$post.excerptcatlinks'
),

'content.excerpts.excerpt.catlinks.item' => array(
'data' => &$excerpt['catlinks']['item'],
'tag' => '$item',
'replace' => '$items'
),

'content.excerpts.excerpt.catlinks.divider' => array(
'data' => &$excerpt['catlinks']['divider'],
'tag' => '$divider',
'replace' => ''
),

'content.excerpts.excerpt.taglinks' => array(
'data' => &$excerpt['taglinks'][0],
'tag' => '$post.taglinks',
'replace' => '$post.taglinks'
),

'content.excerpts.excerpt.taglinks.item' => array(
'data' => &$excerpt['taglinks']['item'],
'tag' => '$item',
'replace' => '$items'
),

'content.excerpts.excerpt.taglinks.divider' => array(
'data' => &$excerpt['taglinks']['divider'],
'tag' => '$divider',
'replace' => ''
),

'content.excerpts.lite' => array(
'data' => &$content['lite'][0],
'tag' => '$lite',
'replace' => ''
),

'content.excerpts.lite.excerpt' => array(
'data' => &$content['lite']['excerpt'],
'tag' => '$excerpt',
'replace' => '$excerpt'
),

'content.navi' => array(
'data' => &$content['navi'][0],
'tag' => '$navi',
'replace' => ''
),

'content.navi.prev' => array(
'data' => &$content['navi']['prev'],
'tag' => '$prev',
'replace' => '$items'
),

'content.navi.next' => array(
'data' => &$content['navi']['next'],
'tag' => '$next',
'replace' => ''
),

'content.navi.link' => array(
'data' => &$content['navi']['link'],
'tag' => '$link',
'replace' => ''
),

'content.navi.current' => array(
'data' => &$content['navi']['current'],
'tag' => '$current',
'replace' => ''
),

'content.navi.divider' => array(
'data' => &$content['navi']['divider'],
'tag' => '$divider',
'replace' => ''
),

'content.admin' => array(
'data' => null,
'tag' => '$admin',
'replace' => ''
),

'content.admin.editor' => array(
'data' => &$content['admin']['editor'],
'tag' => '$editor',
'replace' => ''
),

'content.admin.area' => array(
'data' => &$content['admin']['area'],
'tag' => '$area',
'replace' => ''
),

'content.admin.text' => array(
'data' => &$content['admin']['text'],
'tag' => '$text',
'replace' => ''
),

'content.admin.combo' => array(
'data' => &$content['admin']['combo'],
'tag' => '$combo',
'replace' => ''
),

'content.admin.checkbox' => array(
'data' => &$content['admin']['checkbox'],
'tag' => '$checkbox',
'replace' => ''
),

'content.admin.hidden' => array(
'data' => &$content['admin']['hidden'],
'tag' => '$hidden',
'replace' => ''
),

'content.admin.form' => array(
'data' => &$content['admin']['form'],
'tag' => '$form',
'replace' => ''
),

'sitebar' => array(
'data' => null,
'tag' => '$template.sitebar',
'replace' => '$template.sitebar'
),

'custom' => array(
'data' => null,
'tag' => '$custom',
'replace' => ''
)
);
}


}//class
?>