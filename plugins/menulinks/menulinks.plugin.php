<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmenulinks extends tplugin {
public $before;
public $after;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->addmap('before', array());
$this->addmap('after', array());
  }

  public function install() {
$menus = tmenus::instance();
$menus->onitems = $this->onmenuitems;
  }
  
  public function uninstall() {
$menus = tmenus::instance();
    $menus->unsubscribeclass($this);
  }
  
public function onmenuitems(&$content) {
$content = $this->getitems(true) . $content . $this->getitems(false);
}

private function getitems($before) {
$items = $before ? $this->before : $this->after;
if(count($items) == 0) return '';
$result = '';
      $theme = ttheme::instance();
        $tml = $theme->templates['menu.item'];
        $args = targs::instance();
        $args->submenu = '';
foreach ($items as $item) {
          $args->add($item);
          $result .= $theme->parsearg($tml, $args);
}
return $result;
}

}//class
