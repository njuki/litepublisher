<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcolorpicker extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
    $parser = tthemeparser::instance();
    $parser->parsed = $this->themeparsed;
    
    $jsmerger = tjsmerger::instance();
    $jsmerger->add('admin', '/plugins/colorpicker/colorpicker.plugin.min.js');
  }
  
  public function uninstall() {
    $parser = tthemeparser::instance();
    $parser->unsubscribeclass($this);
    
    $jsmerger = tjsmerger::instance();
    $jsmerger->deletefile('admin', '/plugins/colorpicker/colorpicker.plugin.min.js');
  }
  
  public function themeparsed(ttheme $theme) {
    if (empty($theme->templates['content.admin.color'])) {
      $about = tplugins::getabout(tplugins::getname(__file__));
      $theme->templates['content.admin.color'] =
      '<p>
      <input type="text" name="$name" id="text-$name" value="$value" size="22" />
      <label for="text-$name"><strong>$lang.$name</strong></label>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="text-$name"
      value="' . $about['changecolor'] . '" />
      </p>';
    }
  }
  
}//class