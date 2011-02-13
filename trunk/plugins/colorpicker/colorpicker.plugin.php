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
    
    $admin = tadminmenus::instance();
    $admin->heads .= $this->gethead();
    $admin->save();
  }
  
  public function uninstall() {
    $parser = tthemeparser::instance();
    $parser->unsubscribeclass($this);
    
    $admin = tadminmenus::instance();
    $admin->heads = trim(str_replace($this->gethead(), '', $admin->heads));
    $admin->save();
  }
  
  public function gethead() {
    //return '<script type="text/javascript" src="$site.files/plugins/colorpicker/colorpicker.plugin.js"></script>';
    return '<script type="text/javascript">' .
    "\n\$(document).ready(function() {\n" .
      "if (\$(\"input[id^='colorbutton']\").length) {\n" .
        '$.getScript("$site.files/plugins/' . basename(dirname(__file__)) . "/colorpicker.plugin.js\");\n" .
      "}\n" .
    "});\n" .
    "</script>";
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