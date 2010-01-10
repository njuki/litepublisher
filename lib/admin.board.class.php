<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminboard extends tadminmenu {
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
global $options;
$result = sprintf('<h2><a href="%s/admin/logout/">logout</a></h2>', $options->url);
    $editor = tposteditor::instance();
    $result .= $editor->shorteditor();
return $result;
  }
  
  public function processform() {
  }
  
}//class
?>