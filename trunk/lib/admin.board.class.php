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
    $editor = tposteditor::instance();
    return $editor->shorteditor();
  }
  
  public function processfosrm() {
  }
  
}//class
?>