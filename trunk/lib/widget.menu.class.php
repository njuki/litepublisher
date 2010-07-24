<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmenuwidget extends twidget {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->cache = 'nocache';
    $this->basename = 'widget.menu';
$this->template = 'submenu';
$this->adminclass = 'tadminorderwidget';
$this->data['title'] = tlocal::$data['default']['submenu'];
}

public function gettitle($id) {
if (litepublisher::$urlmap->context instanceof tmenu) return litepublisher::$urlmap->context->title;
return $this->data['title'];
}

  public function getcontent($idwidget, $sitebar) {
$id = litepublisher::$urlmap->context->id;
$menus = litepublisher::$urlmap->context->owner;
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('submenu', $sitebar);
    // 1 submenu list
    $submenu = '';
    $childs = $menus->getchilds($id);
    foreach ($childs as $child) {
      $submenu .= $this->getitem($tml, $menus->getitem($child), '');
    }
    
$parent = $menus->getparent($id);
    if ($parent == 0) {
      $result = $submenu;
    } else {
      $sibling = $menus->getchilds($parent);
      foreach ($sibling as $iditem) {
        $result .= $this->getitem($tml, $menus->getitem($iditem), $iditem == $id ? $submenu : '');
      }
    }
    
    $parents = $menus->getparents($id);
    foreach ($parents as $parent) {
      $result = $this->getitem($tml, $menus->getitem($parent), $result);
    }

    if ($result == '')  return '';
return sprintf($theme->getwidgetitems('submenu', $sitebar), $result);
  }
  
  private function getitem($tml, $item, $subnodes) {
    $args = targs::instance();
    if ($subnodes != '') $subnodes = "<ul>\n$subnodes</ul>\n";
    $args->add($item);
    $args->count = $subnodes;
    $args->icon = '';
    $theme = ttheme::instance();
    return $theme->parsearg($tml, $args);
  }
  
}//class

?>