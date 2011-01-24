<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsingletagwidget extends  twidget {
public $items;
public $tags;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.singletag';
$this->addmap('items', array());
$this->tags = tcategories::instance();
  }

public function getidwidget($idtag) {
foreach ($this->items as $id => $item) {
if ($idtag == $item['idtag'])  return $id;
}
return false;
}

  public function add($idtag) {
$tag = $this->tags->getitem($idtag);
    $widgets = twidgets::instance();
    $id = $widgets->addext($this, $tag['title'], 'widget');
    $this->items[$id] = array(
'idtag' => $idtag
'maxcount' => 10,
'invertorder' => false
    );
    
    $sidebars = tsidebars::instance();
    $sidebars->add($id);
    $widgets->unlock();
    $this->save();
    //$this->added($id);
    return $id;
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      
      $widgets = twidgets::instance();
      $widgets->delete($id);
      //$this->deleted($id);
    }
  }
  
  public function widgetdeleted($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
public function tagdeleted($idtag) {
if ($idwidget = $this->getidwidget($idtag)) return $this->delete($idwidget);
}

public function gettitle($id) {
if (isset($this->items[$id])) {
if ($tag = $this->tags->getitem($this->items[$id]['idtag'])) {
return $tag['title'];
}
}
return '';
}
  
  public function getcontent($id, $sidebar) {
if (!isset($this->items[$id])) return '';
getitem(
    $items = $this->tags->itemsposts->getposts($this->items[$id]['idtag']);
    if (count($items) == 0) return '';
    $posts = litepublisher::$classes->posts;
    $items = $posts->stripdrafts($items);
    $items = $posts->sortbyposted($items);
if ($this->items[$id]['invertorder']) {
    $items = array_slice($items, 0 - $this->items[$id]['maxcount']);
} else {
    $items = array_slice($items, 0, $this->items[$id]['maxcount']);
}

   if (count($items) == 0) return '';
       $theme = ttheme::instance();
    return $theme->getpostswidgetcontent($items, $sidebar, '');
  }
  
}//class
?>