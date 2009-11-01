<?php

class tmenu extends TItems {
public $tree;

    public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
$this->addmap('tree', array());
    $this->addevents('edited');
  }

public function getdir() {
global $paths;
return $paths['data'] . 'menus' . DIRECTORY_SEPARATOR;
}
   
  public function add(tmenuitem $item) {
    $Linkgen = TLinkGenerator::instance();
    if ($item->url == '' ) {
      $item->url = $Linkgen->createlink($item, 'post');
    } else {
      $title = $item->title;
      $item->title = trim($post->url, '/');
      $item->url = $Linkgen ->createlink($item, 'post');
      $item->title = $title;
    }

        $urlmap = turlmap::instance();
      $item->id = ++$this->autoid;
    $item->idurl = $urlmap->Add($item->url, get_class($item), $item->id);
      $this->lock();
$this->items[$this->autoid[ = $this->items[0];
unset($this->items[0]);
      $this->sort();
      $item->save();
      $this->unlock();
    $this->Added($post->id);
    $urlmap->ClearCache();
    return $item->id;
  }

public function edit(tmenuitem $item) {
    $urlmap = turlmap::instance();
        $oldurl = $urlmap->gitidurl($item->idurl);
    if ($oldurl != $item->url) {
      $Linkgen = TLinkGenerator::instance();
      if ($item->url == '') {
        $item->url = $Linkgen->createlink($item, 'item', false);
      } else {
        $title = $item->title;
        $item->title = trim($item->url, '/');
        $item->url = $Linkgen->Create($item, 'item', false);
        $item->title = $title;
      }

    if ($oldurl != $item->url) {
//check unique url
if (($idurl = $urlmap->idfind($item->url) && ($idurl != $item->idurl)) {
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
  
  public function sort() {
$this->tree = $this->getsubtree(0);
}

private function getsubtree($parent) {
$result = array();
// первый шаг найти всех детей и отсортировать
$sort= array();
    foreach ($this->items as $id => $item) {
      if (($item['parent'] == $parent) && ($item['status'] == 'published') 
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

private function geparents($id) {
$result = array();
$id = $this->items[$id]['parent'];
while ($id != 0) {
array_unshift ($result, $id);
$id = $this->items[$id]['parent'];
}
return $result;
}  

private function getchilds($id) {
if ($id == 0) return $this->tree;
$parents = $this->getparents($id);
$parents[] = $id;
$result = $this->tree;
foreach ($parents as $parent) {
foreach ($tree as $item) {
if ($item['id'] == $parent) {
$result = $item['subitems'];
continue;
}
}
}
return $result;
}

public function getsubmenuwidget($id) {
global $options;
$result = '';
$childs = $this->getchilds($id);
    if (count($childs) == 0) return '';

$theme = ttheme::instance();
    $tml = $theme->getwidgetitem('menu');
$tml .= "\n";
    foreach ($childs as $item) {
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
    foreach ($this->tree as $item) {
      $result .= sprintf($tml, $options. $item['url'], $item['title'], '');
    }
    return $result;
  }

private function getsubmenu(&$tree) {
    $result = '';
$theme = ttheme::instance();
    $tml = $theme->menu['item'];
    foreach ($tree as $item) {
      $subitems = count($item['subitems']) > 0 ? $this->getsubmenu($item['subitems'] : '';
      $result .= sprintf($tml,$options.url . $item['url'], $item['title'], $subitems);
    }
    return $result;
  }

    public function haschilds($id) {
foreach ($this->items as $id => $item) {
if ($item['parent'] == $id) return true;
}
return false;

  }
  
}//class

class tmenuitem extends TItem implements  ITemplate {
const ownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status');l
  
  public function getbasename() {
    return 'menus' . DIRECTORY_SEPARATOR . $this->id;
  }
  
  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
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
return 'menus' . DIRECTORY_SEPARATOR. $this->id;
}

public function __get($name) {
if (in_array($name, self::ownerprops))return $this->owner->items[$id][$name];
return parent::__get($name);
}

public function __set($name, $value) {
if (in_array($name, self::ownerprops))return $this->owner->setvalue($this->id, $name, $value);
parent::__set($name, $value);
}
  
public function getowner() {
return tmenu::instance();
}

  //ITemplate
//public function request($id) {}
public function gethead() {}
  
  public function gettitle() {
    return $this->data['title'];
  }
  
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function GetTemplateContent() {
        $GLOBALS['post'] = &$this;
$theme = ttheme::instance();
    return $theme->parse($theme->menucontent);
  }
  
  public function getsubmenuwidget() {
return $this->owner->getsubmenuwidget($this->id);
}

}//class

?>