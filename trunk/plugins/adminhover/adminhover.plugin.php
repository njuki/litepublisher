<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminhoverplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['hover'] = false;
  }
  
  public function onadminhead(&$head) {
    $template = ttemplate::instance();
$theme = ttheme::instance();
    if ($this->hover && !$template->hover && $theme->menu->hover) {
$template->hover  = true;
$head .= $template->gethovermenuhead();
      }
  }
  
}//class
?>