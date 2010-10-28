<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmetawidget extends twidget {
  public $items;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.meta';
    $this->template = 'meta';
    $this->adminclass = 'tadminmetawidget';
    $this->addmap('items', array());
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['meta'];
  }
  
  public function add($name, $url, $title) {
    $this->items[$name] = array(
    'enabled' => true,
    'url' => $url,
    'title' => $title
    );
    $this->save();
  }
  
  public function delete($name) {
    if (isset($this->items[$name])) {
      unset($this->items[$name]);
      $this->save();
    }
  }
  
  public function getcontent($id, $sitebar) {
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('meta', $sitebar);
    $metaclasses = isset($theme->data['sitebars'][$sitebar]['meta']) ? $theme->data['sitebars'][$sitebar]['meta']['classes'] :
    array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
    
    $args = targs::instance();
    foreach    ($this->items as $name => $item) {
      if (!$item['enabled']) continue;
$args->add($item);
    $args->icon = '';
$args->subitems = '';
    $args->rel = $name;
$args->class = isset($metaclasses[$name]) ? $metaclasses[$name] : '';
    $result .= $theme->parsearg($tml, $args);
    }
    
    if ($result == '') return '';
    return $theme->getwidgetcontent($result, 'meta', $sitebar);
  }
  
}//class
?>