<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsameposts extends tplugin {

 public static function instance() {
  return getinstance(__class__);
 }
 
 protected function CreateDatacreate) {
  parent::create();
if (dbversion) {
} else {
$this->data['rlease'] = 1;
}
 }
 
 public function postchanged() {
if (dbversion) {
} else {
  $this->release += 1;
  $this->save();
}
 }
 
 public function findsame($id) {
if (dbversion) {
} else {
  $result = array();
  $post = tpost::instance($id);
$posts = tposts::instance();
  $list = $post->categories;
  $cats = &TCategories::instance();
  $same = array();
  foreach ($list as $id) {
if (!isset($cats->items[$id])) continue;
   foreach ($cats->items[$id]['items'] as $i) {
    if (($i == $postid) || !isset($posts->archives[$i])) continue;
    if (isset($same[$i])) {
     $same[$i]++;
    } else {
     $same[$i] = 1;
    }
   }
  }
  
  arsort($same);
  $result = array_keys($same);
  $posts = &TPosts::instance();
  $posts->StripDrafts($result);
  $result = array_slice($result, 0, 7);
  $this->items[$postid] = $result;
  $this->Save();
 }
 
 public function onsitebar(&$content, $index) {
  global $classes, $options, $post;
  $result = '';
  if (!isset($this->items[$id])) $this->findsame($id);
  if (count($this->items[$id]) == 0) return $result;
  $result = TLocal::$data['default']['sameposts'];
  $result = "<ul>$result\n";
  foreach ($this->items[$id] as $postid) {
   $post = &TPost::instance($postid);
   $result .= "<li><a href=\"$options->url$post->url\">$post->title</a></li>\n";
  }
  $result .= "</ul>\n";
  
  return $result;
 }
 
}//class
?>