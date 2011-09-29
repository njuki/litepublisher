<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlitecategories extends  tplugin {
public $lite;
  public $expand;

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addmap('lite', array());
    $this->addmap('expand', array());
  }
  
  public function onlite($id, &$lite) {
if (in_array($id, $this->lite)) {
$lite = true;
} elseif (in_array($id, $this->expand)) {
$lite = false;
}
}

}//class