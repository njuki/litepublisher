<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tredirector extends titems {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'redirector';
  }
  
  public function add($from, $to) {
    $this->items[$from] = $to;
    $this->save();
    $this->added($from);
  }
  
  public function get($url) {
    if (isset($this->items[$url])) return $this->items[$url];
    //fix for 2.xx versions
    if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) return sprintf('/comments/%d.xml', $m[1]);
    return false;
  }
  
}//class
?>