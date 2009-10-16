<?php

class ttags extends TCommonTags {
  
  protected function create() {
    parent::create();
$this->table = 'tags';
    $this->basename = 'tags';
    $this->sortname = 'title';
    $this->showcount = false;
    $this->PermalinkIndex = 'tag';
    $this->PostPropname = 'tags';
  }
  
  public static function instance() {
    return GetNamedInstance('tags', __class__);
  }
  
}

?>