<?php

class TRSSPrevNext extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function BeforePostContent($id) {
  global $Options;
  $result = '';
  $posts = &TPosts::Instance();
  $keys = array_keys($posts->archives);
  $i = array_search($id, $keys);
  if ($i < count($keys) -1) {
   $prevpost = &TPost::Instance($keys[$i + 1]);
   $result .= TLocal::$data['post']['prev'];
   $result .= " <a href=\"$Options->url$prevpost->url\">$prevpost->title</a>";
  }
  
  if ($i > 0) {
   $nextpost = &TPost::Instance($keys[$i - 1]);
   if ($result != '') $result .= ' | ';
   $result .= TLocal::$data['post']['next'];
   $result .= " <a href=\"$Options->url$nextpost->url\">$nextpost->title</a>";
  }
  
  if ($result != '') $result = "<p>$result</p>\n";
  return $result;
 }
 
}//class
?>