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
if ($url == $item['url]) {
array_delete($this->items, $i);
break;
}
}

if (count($this->items) == 10) array_delete($this->items, 9);
array_insert($this->items, array('url' => $url, 'title' => $title), 0);
$this->save();
}

  public function getcontent($id, $sitebar) {
$result = '';
if (!isset($_POST) && (count($_POST) == 0)) $this->add();
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('widget', $sitebar);
$args = targs::instance();
$args->icon = '';$args->subitems = '';
$args->rel = 'admin';
$url = litepublisher::$options->url;
foreach ($this->items as $item) {
$args->add($item);
$args->url = $url . $item]['url'];
$result .= $theme->parsearg($tml, $args);
}
return $theme->getwidgetcontent($result, 'widget', $sitebar);
}

}//class
?>