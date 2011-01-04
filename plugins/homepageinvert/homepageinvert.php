<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepageInvert extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getitems() {
    $posts = tposts::instance();
$perpage = litepublisher::$options->perpage;
    $count = $posts->archivescount;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $posts->select("status = 'published'", " order by posted asc limit $from, $perpage");
    } else {
      $to = min($from + $perpage , $count);
      $arch = array_reverse(array_keys($posts->archives));
      return array_slice($arch, $from, $to - $from);
    }
  }
  
  function install() {
$home = thomepage::instance();
$home->onbeforegetitems = $this->getitems;
  }
  
  function uninstall() {
$home = thomepage::instance();
$home->unsubscribeclass($this);
  }
  
}//class
?>