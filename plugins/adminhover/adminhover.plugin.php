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
  
  public function onadminhover(&$hover) {
    $hover = $this->hover;
  }
  
  public function onadminhead(&$head) {
    $template = ttemplate::instance();
    if ($this->hover && !$template->hovermenu) {
      $theme = ttheme::instance();
      if (isset($theme->menu->id)) {
        $template->javaoptions[] = sprintf("idmenu: '%s'", $theme->menu->id);
        $template->javaoptions[] = sprintf("tagmenu: '%s'", $theme->menu->tag);
        $head .=  '<script type="text/javascript" src="' . litepublisher::$options->files . '/js/litepublisher/hovermenu.min.js"></script>' . "\n";
      }
    }
  }
  
}//class
?>