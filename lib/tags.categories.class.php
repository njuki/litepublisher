<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcategories extends tcommontags {
  //public  $defaultid;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'categories';
$this->contents->table = 'catscontent';
$this->itemsposts->table = $this->table . 'items';
    $this->basename = 'categories' ;
  }

public function getdefaultid() {
return $this->options->defaultid;
}
  
  public function setdefaultid($id) {
global $options;
$thisoptions = $this->options;
    if (($id != $thisoptions->defaultid) && $this->itemexists($id)) {
      $thisoptions->defaultid = $id;
$options->save();
    }
  }
  
}//class
?>