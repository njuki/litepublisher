<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TRSSPrevNext extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function beforepost($id, &$content) {
    $post = tpost::instance($id);
    $content .= $post->prevnext;
  }
  
}//class