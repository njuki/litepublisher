<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminhistory extends torderwidget {
  public $items;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.adminlastpages';
    $this->cache = 'nocache';
    $this->adminclass = 'tadminorderwidget';
    $this->addmap('items', array());
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['name'];
  }
  
  public function add() {
    $url =litepublisher::$urlmap->url;
    $title = litepublisher::$urlmap->context->title;
    foreach ($this->items as $i => $item) {
      if ($url == $item['url']) {
        array_delete($this->items, $i);
        break;
      }
    }
    
    if (count($this->items) == 10) array_delete($this->items, 9);
    array_insert($this->items, array('url' => $url, 'title' => $title), 0);
    $this->save();
  }
  
  public function getcontent($id, $sidebar) {
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('widget', $sidebar);
    $args = targs::instance();
    $args->icon = '';$args->subitems = '';
    $args->rel = 'admin';
    $url = litepublisher::$site->url;
    foreach ($this->items as $item) {
      //var_dump($item);
      $args->title = $item['title'];
      $args->anchor = $item['title'];
      $args->url = $url . $item['url'];
      $result .= $theme->parsearg($tml, $args);
    }
    if (!isset($_POST) || (count($_POST) == 0)) $this->add();
    return $theme->getwidgetcontent($result, 'widget', $sidebar);
  }
  
}//class
?>