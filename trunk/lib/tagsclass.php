<?php

class TTags extends TCommonTags {
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'tags';
    $this->sortname = 'name';
    $this->showcount = false;
    $this->PermalinkIndex = 'tag';
    $this->PostPropname = 'tags';
    $this->WidgetClass = 'tagcloud';
  }
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
}

?>