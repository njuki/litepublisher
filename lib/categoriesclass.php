<?php

class TCategories extends TCommonTags {
  //public  $defaultid;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'categories';
$this->contents->table = 'catscontent';
    $this->basename = 'categories' ;
  }
  
  public function setdefaultid($id) {
    if (($id != $this->defaultid) && isset($this->items[$id])) {
      $this->data['defaultid'] = $id;
      $this->save();
    }
  }
  
}//class
?>