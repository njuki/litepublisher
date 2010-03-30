<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocplugin extends tplugin {
  public $doc;
  public $doctable;
  
  public static function instance($id = 0) {
    return parent::iteminstance('post', __class__, $id);
  }
  
public function filter($post, $content) {
if (!strbegin($content, '[document]')) return;
$filter = tcodedocfilter::instance();
$filter->convert($post, $content);
if ($post->id == 0) $this->fix[] = $post;
return true;
}

}//class
?>