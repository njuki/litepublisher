<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsapeplugin extends tadminwidget {
  private $widgets = array('tcategories', 'TArchives', 'TLinksWidget', 'TFoaf', 'TPosts', 'TMetaWidget');

  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
$this->widget = tsapeplugin::instance();
}

  protected function dogetcontent(twidget $widget, targs $args){
    $theme = ttheme::instance();
    $checkbox = '<p><input type="checkbox" name="widget-$id" id="widget-$id" value="$id" $checked/>
    <label for="widget-$id">$name</label></p>';
    
    $checkboxes = '';
$widgets = twidgets::instance();
    foreach ($$widgets->items as $id => $item) {
      if ($item['ajax']) continue;
      $args->id = $item['id'];
      $args->checked = in_array($item['id'], $plugin->widgets);
      $args->name = $std->gettitle($name);
      $checkboxes .= $theme->parsearg($checkbox, $args);
    }
    
    $args->checkboxes = $checkboxes;
    $args->user = $plugin->user;
    $args->count = $plugin->count;
    $args->force = $plugin->force;
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'sapeform.tml');
    return $theme->parsearg($tml, $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->widgets = array();
    foreach ($_POST as $name => $value) {
      if (strbegin($name, 'widget-')) $widget->widgets[] = (int) $value;
    }
    extract($_POST);
    $widget->count = (int) $count;
    $widget->user = $user;
    $widget->force = isset($force);
  }
  
}//class
?>