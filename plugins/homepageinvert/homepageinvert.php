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
  $posts = tposts::instance();
//    return $Posts->GetPublishedRange(litepublisher::$urlmap->page, litepublisher::$options->postsperpage);
    $count = $posts->archivescount;
    $from = (litepublisher::$urlmap->page - 1) * litepublisher::$options->postsperpage;
    if ($from > $count)  return array();
    if (dbversion)  {
      return $posts->select("status = 'published'", " order by posted asc limit $from, litepublisher::$options->postsperpage");
    } else {
      $to = min($from + litepublisher::$options->postsperpage , $count);
  $arch = array_reverse(array_keys($posts->archives));
      return array_slice($arch, $from, $to - $from);
    }
  }

function install() {
 litepublisher::$urlmap = turlmap::instance();
if (dbversion) {
$item = litepublisher::$urlmap->db->finditem("url = '/'");
litepublisher::$urlmap->setvalue($item['id'], 'class', get_class($this));
} else {
 litepublisher::$urlmap->items['/']['class'] = get_class($this);
litepublisher::$urlmap->save();
}
}

function uninstall() {
$parent = get_parent_class($this);
 litepublisher::$urlmap = turlmap::instance();
if (dbversion) {
$item = litepublisher::$urlmap->db->finditem("url = '/'");
litepublisher::$urlmap->setvalue($item['id'], 'class', $parent);
} else {
 litepublisher::$urlmap->items['/']['class'] = $parent;
litepublisher::$urlmap->save();
}
}

}//class
?>
