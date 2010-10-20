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
$this->parse($theme);
break;

case 'wordpress':
$theme->type = 'wordpress';
break;
}

    $this->parsed($theme);
    return true;
}

public function parse(ttheme $theme) {
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

    $s = file_get_contents($filename);
    $s = str_replace(array("\r\n", "\r", "\n\n"), "\n", $s);
$s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
$s = str_replace('$options.', '$site.', $s);
$this->parsetags($theme, $s);
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
public function find_root_tags($s) {
$result = array();
    $s = str_replace(array("\r\n", "\r"), "\n", $s);
$s = trim($s);
$roottags = array('theme', 'title', 'menu', 'content', 'sitebar');
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
$result[$tag] = $this->extract_tags($tag, $value);
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

public function extract_tags($parent, $s) {
$result = array();
 while (($s != '') && preg_match('/(\$\w*+(\.\w\w*+)+)\s*=\s*(\[|\{|\()?/i', $s, $m)) {
if (!isset($m[3])) $this->error('The baracket not found');
$tag = $m[1];
$j = strpos($s, $m[0]);
$pre  = rtrim(substr($s, 0, $j));
$s= ltrim(substr($s, $j + strlen($m[0])));
$i = self::find_close($s, $m[3]);
$value = trim(substr($s, 0, $i));
$s = ltrim(substr($s, $i + 1));
$result[$tag] = $this->extract_tags($parent . '.' . $tag, $value);
$s = $pre . $tag . $s;
}
$result[0] = trim($s);
return $result;
}

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

}//class
?>