<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class thomepageInvert extends thomepage {
 
 public static function instance() {
  return getinstance(__class__);
 }

 public function getitems() { 
  global $options, $urlmap;
  $posts = tposts::instance();
//    return $Posts->GetPublishedRange($urlmap->page, $options->postsperpage);
    $count = $this->archivescount;
    $from = ($page - 1) * $perpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $posts->select("status = 'published'", " order by posted asc limit $from, $perpage");
    } else {
      $to = min($from + $perpage , $count);
  $arch = array_reverse(array_keys($posts->archives));
      return array_slice($arch, $from, $to - $from);
    }
  }
  
}//class
?>
