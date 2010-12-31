<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
  
  public function beforemenu(&$content, &$hover, $current) {
    if ($hover != $this->hover) {
      $hover = $this->hover;
      $template = ttemplate::instance();
      $template->hover = $hover;
    }
  }
  
}//class
?>