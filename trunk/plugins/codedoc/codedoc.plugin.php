<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocplugin extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }
  
  public function filterpost($post, &$content, &$cancel) {
if (preg_match('/^\s*(classname|interface)\s*[=:]\s*\w*+/im', $content, $m)) {
    $filter = tcodedocfilter::i();
$filter->filter($post, $content, $m[1]);
$cancel = true;
}
  }
  
public function postdeleted($id) {
litepublisher::$db->table = 'codedoc';
litepublisher::$db->delete("id = $id");
}
}//class