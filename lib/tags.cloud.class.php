<?php

class ttags extends TCommonTags {
  
  protected function create() {
    parent::create();
$this->table = 'tags';
    $this->basename = 'tags';
//    $this->sortname = 'title';
//    $this->showcount = false;
    $this->PermalinkIndex = 'tag';
    $this->PostPropname = 'tags';
$this->contents->table = 'tagscontent';
$this->itemsposts->table = $this->table . 'items';
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
}

?>