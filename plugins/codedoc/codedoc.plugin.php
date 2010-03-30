<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedoc extends tplugin {
  public $doc;
  public $doctable;
  
  public static function instance($id = 0) {
    return parent::iteminstance('post', __class__, $id);
  }
  
public function filter($post, $content) {
if (!strbegin($content, '[document]')) return;
}

}//class
?>