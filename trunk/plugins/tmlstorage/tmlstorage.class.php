<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmlstorage extends titems {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'tmlstorage';
  }

public function classdeleted($classname) {
if (isset($this->items[$classname])) {
unset($this->items[$classname]);
$this->save();
}
}
  
  public function get($obj, $key) {
$classname = get_class($obj);
if (isset($this->items[$classname][$key])) return $this->items[$classname][$key];
return '';
}

  public function set($obj, $key, $value) {
$classname = get_class($obj);
if (isset($this->items[$classname])) {
    $this->items[$classname][$key] = $value;
} else {
    $this->items[$classname] = array(
$key => $value
);
}
    $this->save();
  }
  
}//class