<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tembeddedplayers extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['audio'] = '';
    $this->data['video'] = '';
  }
  
  public function themeparsed($theme) {
    $theme->content->excerpts->excerpt->filelist->array['audio'] = $this->audio;
    $theme->content->excerpts->excerpt->filelist->array['video'] = $this->video;
    $theme->content->post->filelist->array['audio'] = $this->audio;
    $theme->content->post->filelist->array['video'] =$this->video;
  }
  
}//class
?>