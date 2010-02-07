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
    $count = $posts->archivescount;
    $from = ($urlmap->page - 1) * $options->postsperpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $posts->select("status = 'published'", " order by posted asc limit $from, $options->postsperpage");
    } else {
      $to = min($from + $options->postsperpage , $count);
  $arch = array_reverse(array_keys($posts->archives));
      return array_slice($arch, $from, $to - $from);
    }
  }

function install() {
 $urlmap = turlmap::instance();
if (dbversion) {
$item = $urlmap->db->finditem("url = '/'");
$urlmap->setvalue($item['id'], 'class', get_class($this));
} else {
 $urlmap->items['/']['class'] = get_class($this);
$urlmap->save();
}
}

function uninstall() {
$parent = get_parent_class($this);
 $urlmap = turlmap::instance();
if (dbversion) {
$item = $urlmap->db->finditem("url = '/'");
$urlmap->setvalue($item['id'], 'class', $parent);
} else {
 $urlmap->items['/']['class'] = $parent;
$urlmap->save();
}
}

}//class
?>
