<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
    $theme->templates['content.excerpts.excerpt.filelist.audio'] = $this->audio;
    $theme->templates['content.excerpts.excerpt.filelist.video'] = $this->video;
    $theme->templates['content.post.filelist.audio'] = $this->audio;
    $theme->templates['content.post.filelist.video'] =$this->video;
  }
  
}//class