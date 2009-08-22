<?php

class TSamePostsExcerpt extends TPlugin {
 public $items;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->AddDataMap('items', array());
 }
 
 public function PostChanged() {
  $this->items = array();
  $this->Save();
 }
 
 public function Find($postid) {
  $result = array();
  $post = &TPost::Instance($postid);
  $list = $post->tags;
  $cats = TTags::Instance();
  $same = array();
  foreach ($list as $id) {
if (!isset($cats->items[$id])) continue;
   foreach ($cats->items[$id]['items'] as $i) {
    if ($i == $postid) continue;
    if (isset($same[$i])) {
     $same[$i]++;
    } else {
     $same[$i] = 1;
    }
   }
  }
  
  arsort($same);
  $result = array_keys($same);
  $posts = &TPosts::Instance();
  $posts->StripDrafts($result);
  $result = array_slice($result, 0, 7);
  $this->items[$postid] = $result;
  $this->Save();
 }
 
 public function postscript($id) {
  global $Options;
  $result = '';
  if (!isset($this->items[$id])) $this->Find($id);
  if (count($this->items[$id]) == 0) return $result;
  $result = TLocal::$data['default']['sameposts'];
  $result = "<ul>$result\n";
  foreach ($this->items[$id] as $postid) {
   $post = &TPost::Instance($postid);
$excerpt = TContentFilter ::GetExcerpt($post->outputcontent, 500);
   $result .= "<li><a href=\"$Options->url$post->url\">$post->title</a><br /><small>$excerpt</small></li>\n";

  }
  $result .= "</ul>\n";
  
  return $result;
 }
 
}//class
?>