<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public static $defaultargs;
  public static $inifiles;
  public $name;
  public $parsing;
  public $templates;
  private $themeprops;
  
  public static function exists($name) {
    return file_exists(litepublisher::$paths->data . 'themes'. DIRECTORY_SEPARATOR . $name . '.php') ||
    file_exists(litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR  . 'about.ini');
  }
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function getinstance($name) {
    if (isset(self::$instances[$name])) return self::$instances[$name];
    $result = getinstance(__class__);
    if ($result->name != '') $result = litepublisher::$classes->newinstance(__class__);
    $result->name = $name;
    $result->load();
    return $result;
  }
  
  public static function getwidgetnames() {
    return array('submenu', 'categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') ;
  }
  
  protected function create() {
    parent::create();
    $this->name = '';
    $this->parsing = array();
    $this->data['type'] = 'litepublisher';
    $this->data['parent'] = '';
    $this->addmap('templates', array());
    $this->templates = array(
    'index' => '',
    'title' => '',
    'menu' => '',
    'content' => '',
    'sidebars' => array(),
    'custom' => array(),
    'customadmin' => array()
    );
    $this->themeprops = new tthemeprops($this);
    if (!isset(self::$defaultargs)) self::set_defaultargs();
  }
  
  public static function set_defaultargs() {
    self::$defaultargs = array(
    '$site.url' => litepublisher::$site->url,
    '$site.files' => litepublisher::$site->files,
  '{$site.q}' => litepublisher::$site->q,
    '$site.q' => litepublisher::$site->q
    );
  }
  
  public function __destruct() {
    unset($this->themeprops, self::$instances[$this->name], $this->templates);
    parent::__destruct();
  }
  
  public function getbasename() {
    return 'themes' . DIRECTORY_SEPARATOR . $this->name;
  }
  
  public function load() {
    if ($this->name == '') return false;
    if (parent::load()) {
      self::$instances[$this->name] = $this;
      return true;
    }
    return $this->parsetheme();
  }
  
  public function parsetheme() {
    if (!file_exists(litepublisher::$paths->themes . $this->name . DIRECTORY_SEPARATOR  . 'about.ini')) {
      $this->error(sprintf('The %s theme not exists', $this->name));
    }
    
    $parser = tthemeparser::i();
    if ($parser->parse($this)) {
      self::$instances[$this->name] = $this;
      $this->save();
    }else {
      $this->error(sprintf('Theme file %s not exists', $filename));
    }
  }
  
  public function __tostring() {
    return $this->templates['index'];
  }
  
  public function __get($name) {
    if (array_key_exists($name, $this->templates)) return $this->themeprops->setpath($name);
    if ($name == 'comment') return $this->themeprops->setpath('content.post.templatecomments.comments.comment');
    if ($name == 'sidebar') return $this->themeprops->setroot($this->templates['sidebars'][0]);
    if (preg_match('/^sidebar(\d)$/', $name, $m)) return $this->themeprops->setroot($this->templates['sidebars'][$m[1]]);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (array_key_exists($name, $this->templates)) {
      $this->templates[$name] = $value;
      return;
    }
    return parent::__set($name, $value);
  }
  
  public function gettag($path) {
    if (!array_key_exists($path, $this->templates)) $this->error(sprintf('Path "%s" not found', $path));
    $this->themeprops->setpath($path);
    $this->themeprops->tostring = true;
    return $this->themeprops;
  }
  
  public function reg($exp) {
    if (!strpos($exp, '\.')) $exp = str_replace('.', '\.', $exp);
    $result = array();
    foreach ($this->templates as $name => $val) {
      if (preg_match($exp, $name)) $result[$name] = $val;
    }
    return $result;
  }
  
  public function getsidebarscount() {
    return count($this->templates['sidebars']);
  }
  
  
  private function  get_author() {
    $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
    if (!is_object($context)) {
      if (!isset(self::$vars['post'])) return new emptyclass();
      $context = self::$vars['post'];
    }
    
    if ($context instanceof     tuserpages) return $context;
    $iduser = 0;
    foreach (array('author', 'idauthor', 'user', 'iduser') as $propname) {
      if (isset($context->$propname)) {
        $iduser = $context->$propname;
        break;
      }
    }
    if (!$iduser) return new emptyclass();
    $pages = tuserpages::i();
    if (!$pages->itemexists($iduser)) return new emptyclass();
    $pages->request($iduser);
    return $pages;
  }
  
  private function getvar($name) {
    switch ($name) {
      case 'site':
      return litepublisher::$site;
      
      case 'lang':
      return tlocal::i();
      
      case 'post':
      $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
      if ($context instanceof tpost) return $context;
      break;
      
      case 'author':
      return self::get_author();
      
      case 'metapost':
      return isset(self::$vars['post']) ? self::$vars['post']->meta : new emptyclass();
    } //switch
    
    if (isset($GLOBALS[$name])) {
      $var =  $GLOBALS[$name];
    } else {
      $classes = litepublisher::$classes;
      $var = $classes->gettemplatevar($name);
      if (!$var) {
        if (isset($classes->classes[$name])) {
          $var = $classes->getinstance($classes->classes[$name]);
        } elseif (isset($classes->items[$name])) {
          $var = $classes->getinstance($name);
        } else {
          $class = 't' . $name;
          if (isset($classes->items[$class])) $var = $classes->getinstance($class);
        }
      }
    }
    
    if (!is_object($var)) {
      litepublisher::$options->trace(sprintf('Object "%s" not found in %s', $name, $this->parsing[count($this->parsing) -1]));
      return false;
    }
    
    return $var;
  }
  
  public function parsecallback($names) {
    $name = $names[1];
    $prop = $names[2];
    if (isset(self::$vars[$name])) {
      $var =  self::$vars[$name];
    } elseif ($name == 'custom') {
      return $this->parse($this->templates['custom'][$prop]);
    } elseif ($var = $this->getvar($name)) {
      self::$vars[$name] = $var;
    } elseif (($name == 'metapost') && isset(self::$vars['post'])) {
      $var = self::$vars['post']->meta;
    } else {
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
    $s = strtr((string) $s, self::$defaultargs);
    array_push($this->parsing, $s);
    try {
      $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
      $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', array($this, 'parsecallback'), $s);
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
  
  public function replacelang($s, $lang) {
    $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string) $s);
    self::$vars['lang'] = isset($lang) ? $lang : tlocal::i('default');
    $s = strtr($s, self::$defaultargs);
    if (preg_match_all('/\$lang\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $name = $item[1];
      if ($v = $lang->{$name}) {
          $s = str_replace($item[0], $v, $s);
        }
      }
    }
    return $s;
  }
  
  public static function parsevar($name, $var, $s) {
    self::$vars[$name] = $var;
    return self::i()->parse($s);
  }
  
  public function gethtml($context) {
    self::$vars['context'] = $context;
    switch ($this->type) {
      case 'litepublisher':
      return $this->parse($this->templates['index']);
      
      case 'wordpress':
      return wordpress::getcontent();
    }
  }
  
  public function getnotfount() {
    return $this->parse($this->templates['content.notfound']);
  }
  
  public function getpages($url, $page, $count, $params = '') {
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
    $args = new targs();
    $args->count = $count;
    $from = 1;
    $to = $count;
    $perpage = litepublisher::$options->perpage;
    $args->perpage = $perpage;
    $items = array();
    if ($count > $perpage * 2) {
      //$page is midle of the bar
      $from = (int) max(1, $page - ceil($perpage / 2));
      $to = (int) min($count, $from + $perpage);
    }
    
    if ($from == 1) {
      $items = range($from, $to);
    } else {
      $items[0] = 1;
      if ($from > $perpage) {
        if ($from - $perpage  - 1 < $perpage) {
          $items[] = $perpage;
        } else {
          array_splice($items, count($items), 0, range($perpage, $from - 1, $perpage));
        }
      }
      array_splice($items, count($items), 0, range($from, $to));
    }
    
    if ($to < $count) {
      $from2 = (int) ($perpage * ceil(($to+1) / $perpage));
      if ($from2 + $perpage >= $count) {
        if ($from2 < $count) $items[] = $from2;
      } else {
        array_splice($items, count($items), 0, range($from2, $count, $perpage));
      }
      if ($items[count($items) -1] != $count) $items[] = $count;
    }
    
    $currenttml=$this->templates['content.navi.current'];
    $tml =$this->templates['content.navi.link'];
    if (!strbegin($url, 'http')) $url = litepublisher::$site->url . $url;
    $pageurl = rtrim($url, '/') . '/page/';
    
    $a = array();
    foreach ($items as $i) {
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl .$i . '/';
      if ($params) $link .= litepublisher::$site->q . $params;
      $args->link = $link;
      $a[] = $this->parsearg(($i == $page ? $currenttml : $tml), $args);
    }
    
    $args->link =$url;
    $args->pageurl = $pageurl;
    $args->page = $page;
    $args->items = implode($this->templates['content.navi.divider'], $a);
    return $this->parsearg($this->templates['content.navi'], $args);
  }
  
  public function getposts(array $items, $lite) {
    if (count($items) == 0) return '';
    if (dbversion) tposts::i()->loaditems($items);
    
    $result = '';
    self::$vars['lang'] = tlocal::i('default');
    //$tml = $lite ? $this->templates['content.excerpts.lite.excerpt'] : $this->templates['content.excerpts.excerpt'];
    foreach($items as $id) {
      $post = tpost::i($id);
      $result .= $post->getcontexcerpt($lite);
      // has $author.* tags in tml
      if (isset(self::$vars['author'])) unset(self::$vars['author']);
    }
    
    $tml = $lite ? $this->templates['content.excerpts.lite'] : $this->templates['content.excerpts'];
    if ($tml != '') $result = str_replace('$excerpt', $result, $this->parse($tml));
    unset(self::$vars['post']);
    return $result;
  }
  
  public function getpostsnavi(array $items, $lite, $url, $count, $liteperpage = 1000) {
    $result = $this->getposts($items, $lite);
    $perpage = $lite ? $liteperpage : litepublisher::$options->perpage;
    $result .= $this->getpages($url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
  
  public function getpostswidgetcontent(array $items, $sidebar, $tml) {
    if (count($items) == 0) return '';
    $result = '';
    if ($tml == '') $tml = $this->getwidgetitem('posts', $sidebar);
    foreach ($items as $id) {
      self::$vars['post'] = tpost::i($id);
      $result .= $this->parse($tml);
    }
    unset(self::$vars['post']);
    return str_replace('$item', $result, $this->getwidgetitems('posts', $sidebar));
  }
  
  public function getwidgetcontent($items, $name, $sidebar) {
    return str_replace('$item', $items, $this->getwidgetitems($name, $sidebar));
  }
  
  public function getwidget($title, $content, $template, $sidebar) {
    $args = new targs();
    $args->title = $title;
    $args->items = $content;
    return $this->parsearg($this->getwidgettml($sidebar, $template, ''), $args);
  }
  
  public function  getwidgetitem($name, $index) {
    return $this->getwidgettml($index, $name, 'item');
  }
  
  public function  getwidgetitems($name, $index) {
    return $this->getwidgettml($index, $name, 'items');
  }
  
  public function  getwidgettml($index, $name, $tml) {
    $count = count($this->templates['sidebars']);
    if ($index >= $count) $index = $count - 1;
    $widgets = &$this->templates['sidebars'][$index];
    if (($tml != '') && ($tml [0] != '.')) $tml = '.' . $tml;
    if (isset($widgets[$name . $tml])) return $widgets[$name . $tml];
    if (isset($widgets['widget' . $tml])) return $widgets['widget'  . $tml];
    $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
  }
  
  public function getajaxtitle($id, $title, $sidebar, $tml) {
    $args = new targs();
    $args->title = $title;
    $args->id = $id;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->templates[$tml], $args);
  }
  
  
  public function simple($content) {
    return str_replace('$content', $content, $this->templates['content.simple']);
  }
  
  public static function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
  }
  
  public static function cacheini($filename) {
    if (isset(self::$inifiles[$filename])) return self::$inifiles[$filename];
    $datafile = tlocal::getcachedir() . sprintf('cacheini.%s.php', md5($filename));
    if (!tfilestorage::loadvar($datafile, $ini) || !is_array($ini)) {
      if (file_exists($filename)) {
        $ini = parse_ini_file($filename, true);
        tfilestorage::savevar($datafile, $ini);
      } else {
        $ini = array();
      }
    }
    
    if (!isset(self::$inifiles)) self::$inifiles = array();
    self::$inifiles[$filename] = $ini;
    return $ini;
  }
  
  public static function inifile($class, $filename) {
    $dir = litepublisher::$classes->getresourcedir($class);
    return self::cacheini($dir . $filename);
  }
  
  public static function getwidgetpath($path) {
    if ($path === '') return '';
    switch ($path) {
      case '.items':
      return '.items';
      
      case '.items.item':
      case '.item':
      return '.item';
      
      case '.items.item.subcount':
      case '.item.subcount':
      case '.subcount':
      return '.subcount';
      
      case '.items.item.subitems':
      case '.item.subitems':
      case '.subitems':
      return '.subitems';
      
      case '.classes':
      case '.items.classes':
      return  '.classes';
    }
    
    return false;
  }
  
}//class

class tthemeprops {
  
  public $path;
  public $tostring;
  private $root;
  private $theme;
  
  public function __construct(ttheme $theme) {
    $this->theme = $theme;
    $this->root = &$theme->templates;
    $this->path = '';
    $this->tostring = false;
  }
  
  public function __destruct() {
    unset($this->theme, $this->root);
  }
  
  public function error($path) {
    litepublisher::$options->trace(sprintf('Path "%s" not found', $path));
    litepublisher::$options->showerrors();
  }
  
  public function getpath($name) {
    return $this->path == '' ? $name : $this->path . '.' . $name;
  }
  
  public function setpath($path) {
    $this->root = &$this->theme->templates;
    $this->path = $path;
    $this->tostring = false;
    return $this;
  }
  
  public function setroot(array &$root) {
    $this->setpath('');
    $this->root = &$root;
    return $this;
  }
  
  public function __get($name) {
    //echo "$name get tml<br>";
    $path = $this->getpath($name);
    if (!array_key_exists($path, $this->root)) $this->error($path);
    if ($this->tostring) return $this->root[$path];
    $this->path = $path;
    return $this;
  }
  
  public function __set($name, $value) {
    $this->root[$this->getpath($name)] = $value;
  }
  
  public function __call($name, $params) {
    if (isset($params[0]) && is_object($params[0]) && ($params[0] instanceof targs)) {
      return $this->theme->parsearg( (string) $this->$name, $params[0]);
    } else {
      return $this->theme->parse((string) $this->$name);
    }
  }
  
  public function __tostring() {
    if (array_key_exists($this->path, $this->root)) {
      return $this->root[$this->path];
    } else {
      $this->error($this->path);
    }
  }
  
  public function __isset($name) {
    return array_key_exists($this->getpath($name), $this->root);
  }
  
}//class

class targs {
  public $data;
  
  public static function i() {
    return litepublisher::$classes->newinstance(__class__);
  }
  
  public function __construct($thisthis = null) {
    if (!isset(ttheme::$defaultargs)) ttheme::set_defaultargs();
    $this->data = ttheme::$defaultargs;
    
    if (isset($thisthis)) $this->data['$this'] = $thisthis;
  }
  
  public function __get($name) {
    if (($name == 'link') && !isset($this->data['$link'])  && isset($this->data['$url'])) {
      return litepublisher::$site->url . $this->data['$url'];
    }
    return $this->data['$' . $name];
  }
  
  public function __set($name, $value) {
    if (!is_string($name)) return;
    if ($name == '') return;
    if (is_bool($value)) {
      $value = $value ? 'checked="checked"' : '';
    }
    
    $this->data['$'.$name] = $value;
    $this->data["%%$name%%"] = $value;
    
    if (($name == 'url') && !isset($this->data['$link'])) {
      $this->data['$link'] = litepublisher::$site->url . $value;
      $this->data['%%link%%'] = litepublisher::$site->url . $value;
    }
  }
  
  public function add(array $a) {
    foreach ($a as $key => $value) {
      $this->__set($key, $value);
      if ($key == 'url') {
        $this->data['$link'] = litepublisher::$site->url . $value;
        $this->data['%%link%%'] = litepublisher::$site->url . $value;
      }
    }
    
    if (isset($a['title']) && !isset($a['text'])) $this->__set('text', $a['title']);
    if (isset($a['text']) && !isset($a['title']))  $this->__set('title', $a['text']);
  }
  
}//class

class emptyclass{
public function __get($name) { return ''; }
}