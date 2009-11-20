<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcustomwidget extends titems {
  
  public static function instance() {
    return instance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename   = 'customwidget';
  }
  
  public function getwidget($id, $sitebar) {
    global $options;
$item = $this->getitem($id);
    if (!$item['templ']) return $item['content'];
$theme = ttheme::instance();
return $theme->getwidget($item['title'], $item['content'], 'widget', $sitebar);
}
  
  public function add($title, $content, $templ) {
$widgets = twidgets::instance();
    $id = $widgets->add(get_class($this), 'echo', '', '');
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
    'templ' => $templ
    );
    
    $this->save();
    $this->added($id);
    return $id;
  }
  
  public function edit($id, $title, $content, $templ) {
    $this->items[$id] = array(
    'title' => $title,
    'content' => $content,
    'templ' => $templ
    );
    
    $this->save();
$widgets = twidgets::instance();
$widgets->itemexpired($id);
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