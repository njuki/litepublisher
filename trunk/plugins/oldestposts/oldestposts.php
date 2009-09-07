<?php

class TOldestPosts extends TPlugin {
 public $items;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function postscript($id) {
  global $classes, $post, $Options;
    if (!is_a($post, $classes->classes['post'])) return '';
  $result = '';
$posts = TPosts::Instance();
$arch = array_keys($posts->archives);
$i = array_search($id, $arch);
if (!is_int($i)) return '';
$items = array_slice($arch, $i + 1, 10);
  if (count($items) == 0) return $result;
  $result = TLocal::$data['default']['prev'];
  $result = "<ul>$result\n";
  foreach ($items as $postid) {
   $post = &TPost::Instance($postid);
   $result .= "<li><a href=\"$Options->url$post->url\">$post->title</a></li>\n";
  }
  $result .= "</ul>\n";
  
  return $result;
 }
 
}//class
?>