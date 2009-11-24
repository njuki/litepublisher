<?php 
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminmenus extends tmenus {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'adminmenu';
tadminmenu::$ownerprops = array_merge(tadminmenu::$ownerprops, array('name', 'group'));
}

public function add($parent, $name, $group, $class) {
if (isset(tlocal::$data[$name]['title'])) {
$title = tlocal::$data[$name]['title'];
} elseif (isset(tlocal::$data['names'][$name])) {
$title = tlocal::$data['names'][$name];
} elseif (isset(tlocal::$data['default'][$name])) {
$title = tlocal::$data['default'][$name];
} elseif (isset(tlocal::$data['common'][$name])) {
$title = tlocal::$data['common'][$name];
} else {
$title= $name;
echo "$name not found\n";
}

$url = $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
$urlmap = turlmap::instance();
$this->items[++$this->autoid] = array(
'id' => $this->autoid,
'parent' => $parent,
'order' => $this->autoid,
'url' => $url,
'idurl' => $urlmap->add($url, $class, null, 'tree'),
'title' => $title,
'status' => 'published',
'name' => $name,
'group' => $group
);
$this->sort();
$this->save();

}

public function getsubmenuwidget($id) {
global $options;
$result = '';
$childs = $this->getchilds($id);
    if (count($childs) == 0) return '';

$theme = ttheme::instance();
    $tml = $theme->getwidgetitem('menu');
$tml .= "\n";
$groups = Tusergroups::instance();
    foreach ($childs as $item) {
if ($groups->hasright($options->group, $item['group'])) 
      $result .= sprintf($tml, $options->url . $item['url'], $item['title'], '');
    }

$sitebars = tsitebars::instance();    
    return $theme->getwidget($this->items[$id]['title'], $result, 'submenu', $sitebars->current);
  }

public function getmenu($hover) {
global $options;
    if (count($this->tree) == 0) return '';
if ($hover) return $this->getsubmenu($this->tree);

    $result = '';
$theme = ttheme::instance();
    $tml = $theme->menu['item'];
$groups = Tusergroups::instance();
    foreach ($this->tree as $item) {
if ($groups->hasright($options->group, $item['group']))
      $result .= sprintf($tml, $options. $item['url'], $item['title'], '');
    }
    return $result;
  }

private function getsubmenu(&$tree) {
    $result = '';
$theme = ttheme::instance();
    $tml = $theme->menu['item'];
$groups = Tusergroups::instance();
    foreach ($tree as $item) {
if ($groups->hasright($options->group, $item['group'])) {
      $subitems = count($item['subitems']) == 0 ? '' : $this->getsubmenu($item['subitems']);
      $result .= sprintf($tml,$options.url . $item['url'], $item['title'], $subitems);
}
    }
    return $result;
  }

}//class

class tadminmenu  extends tmenu {
public $arg;

  protected function create() {
    parent::create();
    $this->cache = false;
  }
  
public function load() {}
public function save() {}

public function getowner() {
return tadminmenu::instance();
}

  public function auth() {
    global $options, $urlmap;
    $auth = tauthdigest::instance();
    if ($auth->cookieenabled) {
if ($s = $auth->checkattack()) return $s;
if (!$auth->authcookie()) return $urlmap->redir301('/admin/login/');
      } 
elseif (!$auth->Auth())  return $auth->headers();      

if ($options->group != 'admin') {
$groups = tusergroups::instance();
if ($groups->hasright($options->group, $this->group)) return 404;
}
  }
  
  public function request($id) {
    if ($s = $this->auth()) return $s;
    tlocal::loadlang('admin');
      $this->data['id'] = $id;
if ($id > 0) {
$this->basename =  $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
}
$urlmap = turlmap::instance();
$this->arg = $urlmap->argtree;
$this->checkform();
  }
  
  public function idget() {
    return !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
  }
  
public function gethtml($name = '') {
if ($name == '') $name = $this->basename;
if (!isset(tlocal::$data[$name])) {
$name = $this->owner->items[$this->parent]['name'];
}

$result = THtmlResource::instance();
$result->section = $name;
$lang = tlocal::instance($name);
return $result;
}

public function getlang() {
return tlocal::instance($this->name);
}

  public function Getconfirmed() {
    return !isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function FixCheckall($s) {
    return str_replace('checkAll(document.getElementById("form"));', "checkAll(document.getElementById('form'));",    str_replace("'", '"', $s));
  }
  
  public function getnotfound() {
return $this->html->h2->notfound;
  }
  
  public function getadminurl() {
    global $options;
return $options->url .$this->url . $options->q . '=';
}

 }//class
?>