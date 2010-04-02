<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminboard extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getgroup() {
    return 'editor';
  }
  
  
  
  public function gethead() {
    $editor = tposteditor::instance();
    return $editor->gethead();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $editor = tposteditor::instance();
    $result .= $editor->shorteditor();
    return $result;
  }
  
  public function processform() {
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
}//class
?>