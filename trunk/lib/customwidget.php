<?php

class tcustomwidget extends TItems {
  
  public static function instance() {
    return instance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename   = 'customwidget';
  }
  
  public function getwidget($id, $sitebar) {
    global $options;
    if (!$this->items[$id]['templ']) return $this->items[$id]['content'];
$theme = ttheme::instance();
return $theme->getwidget($this->items[$id]['title']), $this->items[$id]['content'], 'widget', $sitebar);
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