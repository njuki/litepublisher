<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmenus extends titems {
  public $tree;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('edited', 'onprocessform', 'onmenu', 'onitems', 'onsubitems');
    
    $this->dbversion = false;
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
    $this->addmap('tree', array());
$this->data['idhome'] = 0;
$this->data['home'] = false;
  }
  
  public function getlink($id) {
    return sprintf('<a href="%1$s%2$s" title="%3$s">%3$s</a>', litepublisher::$site->url, $this->items[$id]['url'], $this->items[$id]['title']);
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'menus' . DIRECTORY_SEPARATOR;
  }
  
  public function add(tmenu $item) {
    //fix null fields
    foreach (tmenu::$ownerprops as $prop) {
      if (!isset($item->data[$prop])) $item->data[$prop] = '';
    }
    
if ($item instanceof thomepage) {
$item->url = '/';
} else {
    $linkgen = tlinkgenerator::instance();
    $item->url = $linkgen->addurl($item, 'menu');
}

    $id = ++$this->autoid;
    $this->items[$id] = array(
    'id' => $id,
    'class' => get_class($item)
    );
    //move props
    foreach (tmenu::$ownerprops as $prop) {
      $this->items[$id][$prop] = $item->$prop;
      if (array_key_exists($prop, $item->data)) unset($item->data[$prop]);
    }
    $item->id = $id;
    $urlmap = turlmap::instance();
    $item->idurl = $urlmap->Add($item->url, get_class($item), $item->id);
    if ($item->status != 'draft') $item->status = 'published';
    $this->lock();
    $this->sort();
    $item->save();
    $this->unlock();
    $this->added($id);
    $urlmap->clearcache();
    return $id;
  }
  
  public function additem(array $item) {
    $item['id'] = ++$this->autoid;
    $item['order'] = $this->autoid;
    $item[    'status'] = 'published';
    if ($idurl = litepublisher::$urlmap->urlexists($item['url'])) {
      $item['idurl'] =  $idurl;
    } else {
      $item['idurl'] =litepublisher::$urlmap->add($item['url'], $item['class'], $this->autoid, 'get');
    }
    
    $this->items[$this->autoid] = $item;
    $this->sort();
    $this->save();
    litepublisher::$urlmap->clearcache();
    return $this->autoid;
  }
  
  public function insert($class, $parent, $title, $url) {
    return $this->additem(array(
    'parent' => (int)$parent,
    'title' => $title,
    'url' => $url,
    'class' => $class
    ));
  }
  
  public function edit(tmenu $item) {
if (!($item instanceof thomepage)) {
    $linkgen = tlinkgenerator::instance();
    $linkgen->editurl($item, 'menu');
}
    
    $this->lock();
    $this->sort();
    $item->save();
    $this->unlock();
    $this->edited($item->id);
    litepublisher::$urlmap->clearcache();
  }
  
  public function  delete($id) {
    if (!$this->itemexists($id)) return false;
if($id == $this->idhome) return false;
    if ($this->haschilds($id)) return false;
    // save homepage
    $url = $this->items[$id]['url'];
    if ($url != '/') litepublisher::$urlmap->delete($url);
    $this->lock();
    unset($this->items[$id]);
    $this->sort();
    $this->unlock();
    $this->deleted($id);
    @unlink($this->dir . "$id.php");
    @unlink($this->dir . "$id.bak.php");
    litepublisher::$urlmap->clearcache();
    return true;
  }
  
  public function deleteurl($url) {
    if ($id = $this->url2id($url)) return $this->delete($id);
  }
  
  public function url2id($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['url']) return $id;
    }
    return false;
  }
  
  public function  remove($id) {
    if (!$this->itemexists($id)) return false;
    if ($this->haschilds($id)) return false;
    $this->lock();
    unset($this->items[$id]);
    $this->sort();
    $this->unlock();
    $this->deleted($id);
    litepublisher::$urlmap->clearcache();
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
    // first step is a find all childs and sort them
    $sort= array();
    foreach ($this->items as $id => $item) {
      if (($item['parent'] == $parent) && ($item['status'] == 'published')) {
        $sort[$id] = (int) $item['order'];
      }
    }
    arsort($sort, SORT_NUMERIC);
    $sort = array_reverse($sort, true);
    
    foreach ($sort as $id => $order) {
      $result[$id]  = $this->getsubtree($id);
    }
    return $result;
  }
  
  
  public function getparent($id) {
    return $this->items[$id]['parent'];
  }
  
  //return array of id
  public function getparents($id) {
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
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      foreach ($this->tree as $iditem => $items) {
        $result[] = $iditem;
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
      foreach ($tree as $iditem => $items) {
        if ($iditem == $parent) {
          $tree = $items;
          break;
        }
      }
    }
    return array_keys($tree);
  }

  public function exclude($id) {
return !$this->home && ($id == $this->idhome);
}

  public function getmenu($hover, $current) {
$result = '';
    if (count($this->tree) > 0) {
    $theme = ttheme::instance();
    if ($hover) {
      $items = $this->getsubmenu($this->tree, $current);
    } else {
$items = '';
    $tml = $theme->menu->item;
    $args = targs::instance();
    $args->submenu = '';
    foreach ($this->tree as $id => $subitems) {
if ($this->exclude($id)) continue;
      $args->add($this->items[$id]);
      $items .= $current == $id ? $theme->parsearg($theme->menu->current, $args) : $theme->parsearg($tml, $args);
    }
}

      $this->callevent('onitems', array(&$items));
      $result = str_replace('$item', $items, (string) $theme->menu);
}
      $this->callevent('onmenu', array(&$result));
return $result;
  }
  
  private function getsubmenu(&$tree, $current) {
    $result = '';
    $theme = ttheme::instance();
    $menu = $theme->menu;
    $tml = $menu->item;
    $args = targs::instance();
    foreach ($tree as $id => $items) {
if ($this->exclude($id)) continue;
$submenu = count($items) == 0 ? '' :  str_replace('$items', $this->getsubmenu($items, $current), $menu->submenu);
      $this->callevent('onsubitems', array($id, &$subitems));
      $args->submenu = $submenu;
      $args->add($this->items[$id]);
      $result .= $theme->parsearg($current == $id ?  $menu->current : $tml, $args);
    }
    return $result;
  }
  
  public function class2id($class) {
    foreach($this->items as $id => $item) {
      if ($class == $item['class']) return $id;
    }
    return false;
  }
  
}//class

class tmenu extends titem implements  itemplate {
  public static $ownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status');
  public $formresult;
  
  public static function instance($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  public static function iteminstance($class, $id = 0) {
    $single = getinstance($class);
    if ($single->id == $id) return $single;
    if (($single->id == 0) && ($id > 0)) return $single->loaddata($id);
    return parent::iteminstance($class, $id);
  }
  
  public static function getinstancename() {
    return 'menu';
  }
  
  public static function getowner() {
    return tmenus::instance();
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
    'view' => 1,
    //owner props
    'title' => '',
    'url' => '',
    'idurl' => 0,
    'parent' => 0,
    'order' => 0,
    'status' => 'published'
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
  
  public function __isset($name) {
    if (in_array($name, self::$ownerprops)) return true;
    return parent::__isset($name);
  }
  
  //ITemplate
  public function request($id) {
    parent::request($id);
    if ($this->status == 'draft') return 404;
    $this->doprocessform();
  }
  
  protected function doprocessform() {
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

public function getview() {
return $this->data['view'];
}

public function setview($id) {
if ($id != $this->view) {
$this->data['view'] = $id;
$this->save();
}

    public function getcont() {
    return ttheme::parsevar('menu', $this, ttheme::instance()->content->menu);
  }
  
  public function getlink() {
    return litepublisher::$site->url . $this->url;
  }

public function getcontent() {
return $this->data['content'];
}

public function setcontent($s) {
    if (!is_string($s)) $this->error('Error! Page content must be string');
    if ($s != $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = tcontentfilter::instance();
      $this->data['content'] = $filter->filter($s);
    }
}
  
}//class

?>