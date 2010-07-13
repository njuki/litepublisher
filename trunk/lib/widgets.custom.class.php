<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcustomwidget extends twidget {
public $items;
private $id;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename   = 'widgets.custom';
$this->addmap('items', array());
  }

public function getwidget($id, $sitebar) {
if (!isset($this->items[$id])) return '';
if ($this->items[$id]['template'] == '') return $this->items[$id]['content'];
return parent::getwidget($id, $sitebar);
}

public function gettitle($id) {
return $this->items[$id]['title'];
}
  
  public function getcontent($id, $sitebar) {
return $this->items[$id]['content'];
  }
  
  public function add($title, $content, $template) {
    $widgets = twidgets::instance();
    $id = $widgets->addext($this, $title, $template);
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
'template' => $template
    );
    
    $this->save();
    $this->added($id);
    return $id;
  }
  
  public function edit($id, $title, $content, $template) {
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
'template' => $template
    );
        $this->save();
    $widgets->expired($id);
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      
      $widgets = twidgets::instance();
      $widgets->delete($id);
      $this->deleted($id);
    }
  }
  
  public function widgetdeleted($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
} //class
?>