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
    parent::create();
    $this->basename = 'redirector';
    $this->dbversion = false;
  }
  
  public function add($from, $to) {
    $this->items[$from] = $to;
    $this->save();
    $this->added($from);
  }
  
}
?>