<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class ttags extends tcommontags {
  
  protected function create() {
    parent::create();
$this->table = 'tags';
    $this->basename = 'tags';
    $this->sortname = 'title';
    $this->showcount = false;
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