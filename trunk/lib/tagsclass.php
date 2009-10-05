<?php

class TTags extends TCommonTags {
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'tags';
    $this->sortname = 'name';
    $this->showcount = false;
    $this->PermalinkIndex = 'tag';
    $this->PostPropname = 'tags';
  }
  
  public static function &Instance() {
    return GetNamedInstance('tags', __class__);
  }
  
}

?>