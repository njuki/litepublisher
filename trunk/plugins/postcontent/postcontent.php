<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tpostcontentplugin extends tplugin {

 public static function instance() {
  return getinstance(__class__);
 }
 
 protected function create() {
  parent::create();
$this->data['before'] = '';
$this->data['after'] = '';
 }

public function beforecontent($id, &$content) {
$content = $this->before . $content;
}

public function aftercontent($id, &$content) {
$content .= $this->after;
}
 
}//class
?>