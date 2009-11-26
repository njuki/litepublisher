<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tmenus extends TItems {
public $tree;

    public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('edited', 'onprocessform');

$this->dbversion = false;
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
$this->addmap('tree', array());
  }

public function getlink($id) {
global $options;
return sprintf('<a href="%1$s%2$s" title="%3$s">%3$s</a>', $options->url, $this->items[$id]['url'], $this->items[$id]['title']);
}

public function getdir() {
global $paths;
return $paths['data'] . 'menus' . DIRECTORY_SEPARATOR;
}
   
  public function add(imenu $item) {
//fix null fields
foreach (tmenu::$ownerprops as $prop) {
if (!isset($item->data[$prop])) $item->data[$prop] = '';
}

    $linkgen = tlinkgenerator::instance();
    if ($item->url == '' ) {
      $item->url = $linkgen->createlink($item, 'post', true);
    } else {
      $title = $item->title;
      $item->title = trim($post->url, '/');
      $item->url = $linkgen ->createlink($item, 'post', true);
      $item->title = $title;
    }

$this->items[++$this->autoid] = array('id' => $this->autoid);
//move props
foreach (tmenu::$ownerprops as $prop) {
$this->items[$this->autoid][$prop] = $item->$prop;
if (isset($item->data[$prop])) unset($item->data[$prop]);
}
      $item->id = $this->autoid;
        $urlmap = turlmap::instance();
    $item->idurl = $urlmap->Add($item->url, get_class($item), $item->id);
if ($item->status != 'draft') $item->status = 'published';
      $this->lock();
      $this->sort();
      $item->save();
      $this->unlock();
    $this->added($item->id);
    $urlmap->clearcache();
    return $item->id;
  }

public function edit(imenu $item) {
    $urlmap = turlmap::instance();
        $oldurl = $urlmap->gitidurl($item->idurl);
    if ($oldurl != $item->url) {
      $linkgen = tlinkgenerator::instance();
      if ($item->url == '') {
        $item->url = $linkgen->createlink($item, 'item', false);
      } else {
        $title = $item->title;
        $item->title = trim($item->url, '/');
        $item->url = $linkgen->Create($item, 'item', false);
        $item->title = $title;
      }
}

    if ($oldurl != $item->url) {
//check unique url
if (($idurl = $urlmap->idfind($item->url)) && ($idurl != $item->idurl)) {
$item->url = $linkgen->MakeUnique($item->url);
}
$urlmap->setidurl($item->idurl, $item->url);
      $urlmap->addredir($oldurl, $item->url);
    }

    $this->lock();    
    $this->sort();
    $item->save();
    $this->unlock();
        $this->edited($item->id);
    $urlmap->clearcache();
  }

  public function  delete($id) {
    if (!$this->itemexists($id)) return false;
    if ($this->haschilds($id)) return false;
    $urlmap = turlmap::instance();
$urmap->delete($this->items[$id]['url']);
    $this->lock();
    unset($this->items[$id]);
    $this->sort();
    $this->unlock();
    $this->deleted($id);
@unlink($this->dir . "$id.php");
@unlink($this->dir . "$id.bak.php");
    $urlmap->clearcache();
    return true;
  }

    public function haschilds($id) {
foreach ($this->items as $id => $item) {
if ($item['parent'] == $id) return true;
}
return false;

  }
  
  public function sort() {
$this->tree = $this->getsubtree(0);
}

private function getsubtree($parent) {
$result = array();
// первый шаг найти всех детей и отсортировать
$sort= array();
    foreach ($this->items as $id => $item) {
      if (($item['parent'] == $parent) && ($item['status'] == 'published')) 
$sort[$id] = (int) $item['order'];
      }
       arsort($sort, SORT_NUMERIC);
$sort = array_reverse($sort, true);

foreach ($sort as $id => $order) {
$item = $this->items[$id];
$item['subitems'] = $this->getsubtree($id);
$result[]  = $item;
}
return $result;
  }

//возвращает массив id
private function getparents($id) {
$result = array();
$id = $this->items[$id]['parent'];
while ($id != 0) {
//array_unshift ($result, $id);
$result[] = $id;
$id = $this->items[$id]['parent'];
}
return $result;
}  

//ищет в дереве список детей, так как они уже отсортированы
private function getchilds($id) {
if ($id == 0) {
$result = array();
foreach ($this->tree as $item) {
$result[] = $item['id'];
}
return $result;
}
$parents = array($id);
$parent = $this->items[$id]['parent'];
while ($parent != 0) {
array_unshift ($parents, $parent);
$parent = $this->items[$parent]['parent'];
}

$tree = $this->tree;
foreach ($parents as $parent) {
foreach ($tree as $item) {
if ($item['id'] == $parent) {
$tree = $item['subitems'];
break;
}
}
}
return $tree;
}

private function getwidgetitem($tml, $item, $subnodes) {
global $options;
if ($subnodes != '') $subnodes = "<ul>\n$subnodes</ul>\n";
return sprintf($tml, $options->url . $item['url'], $item['title'], $subnodes);
}

public function getsubmenuwidget($id) {
$result = '';
$theme = ttheme::instance();
    $tml = $theme->getwidgetitem('menu');
$tml .= "\n";
// 1 вначале список подменю
$submenu = '';
$childs = $this->getchilds($id);
foreach ($childs as $child) {
$submenu .= $this->getwidgetitem($tml, $this->items[$child], '');
}

$sibling = $this->getchilds($this->items[$id]['parent']);
   foreach ($sibling as $iditem) {
$result .= $this->getwidgetitem($tml, $this->items[$iditem], $iditem == $id ? $submenu : '');
    }

$parents = $this->getparents($id);
foreach ($parents as $parent) {
$result = $this->getwidgetitem($tml, $this->items[$parent], $result);
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
    foreach ($this->tree as $item) {
      $result .= sprintf($tml, $options->url . $item['url'], $item['title'], '');
    }
    return $result;
  }

private function getsubmenu(&$tree) {
    $result = '';
$theme = ttheme::instance();
    $tml = $theme->menu['item'];
    foreach ($tree as $item) {
      $subitems = count($item['subitems']) == 0 ? '' : $this->getsubmenu($item['subitems']);
      $result .= sprintf($tml,$options.url . $item['url'], $item['title'], $subitems);
    }
    return $result;
  }

}//class

class tmenu extends titem implements  itemplate, itemplate2, imenu {
public static $ownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status');
  public $formresult;
  
  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->formresult = '';
    $this->data= array(
'id' => 0,
    'author' => 0, //not supported
    'content' => '',
    'rawcontent' => '',
    'keywords' => '',
    'description' => '',
    'password' => '',
    'template' => '',
    'theme' => '',
    );
  }

  public function getbasename() {
    return 'menus' . DIRECTORY_SEPARATOR . $this->id;
  }
  
public function __get($name) {
    if ($name == 'content') return $this->formresult . $this->getcontent();
if (in_array($name, self::$ownerprops)) {
if ($this->id == 0) {
return $this->data[$name];
} else {
return $this->owner->items[$this->id][$name];
}
}
return parent::__get($name);
}

public function __set($name, $value) {
if (in_array($name, self::$ownerprops)) {
if ($this->id == 0) {
$this->data[$name] = $value;
} else {
$this->owner->setvalue($this->id, $name, $value);
}
return;
}
parent::__set($name, $value);
}
  
public function getowner() {
return tmenus::instance();
}

  //ITemplate
public function request($id) {
parent::request($id);
$this->checkform();
}

protected function checkform() {
    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      $this->formresult.= $this->processform();
    }
}

public function processform() {
return $this->owner->onprocessform($this->id);
}

public function gethead() {}
  
  public function gettitle() {
    return $this->__get('title');
  }
  
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }

public function getcontent() {
return $this->data['content'];
}
  
  public function GetTemplateContent() {
global $menu;
$menu = $this;
$theme = ttheme::instance();
    return $theme->parse($theme->menucontent);
  }

//itemplate2
public function getsitebar() {
$result = '';
$sitebars = tsitebars::instance();
$template = ttemplate::instance();
if (($sitebars->current == 0) && !$template->hovermenu) {
$result .= $this->owner->getsubmenuwidget($this->id);
}
    $result .= $sitebars->getcurrent();
return $result;
}

public function afterrequest(&$content) {}

//imenu
public function getparent() {
return $this->__get('parent');
}

public function setparent($id) {
$this->__set('parent', $id);
}

public function getorder() {
return $this->__get('order');
}

public function setorder($order) {
$this->__set('order', $order);
}

}//class

?>