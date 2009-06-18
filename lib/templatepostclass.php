<?php

class TTemplatePost extends TEventClass {
 public $ps; //postscript text
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'templatepost';
  $this->AddEvents('BeforePostContent', 'AfterPostContent', 'Onpostscript');
 }
 
 public function GetPostscript($tagname) {
  global $Options, $post;
  $this->ps = '';;
  if (is_a($post, 'TPost')) {
   if (($post->comments->count > 0) && $post->commentsenabled) {
    $this->ps .= "<p><a href=\"$Options->url/comments/$post->id/\">". TLocal::$data['post']['rsscomments'] . "</a></p>\n";
   }
   
   //prev and next post
   $links = '';
   $posts = &TPosts::Instance();
   $keys = array_keys($posts->archives);
   $i = array_search($post->id, $keys);
   if ($i < count($keys) -1) {
    $prevpost = &TPost::Instance($keys[$i + 1]);
    $links .= TLocal::$data['post']['prev'];
    $links .= " <a href=\"$Options->url$prevpost->url\">$prevpost->title</a>";
   }
   
   if ($i > 0) {
    $nextpost = &TPost::Instance($keys[$i - 1]);
    if ($links != '') $links .= ' | ';
    $links .= TLocal::$data['post']['next'];
    $links .= " <a href=\"$Options->url$nextpost->url\">$nextpost->title</a>";
   }
   
   if ($links != '') $this->ps .= "<p>$links</p>\n";
  }
  $this->ps .= $this->Onpostscript($post->id);
  return $this->ps;
 }
 
 public function PrintPosts(&$Items) {
  global $Template;
  
  if (count($Items) == 0) {
   return 		'<h2 class="center">' . TLocal::$data['default']['notfound'] . '</h2>
   <p class="center">'. TLocal::$data['default']['nocontent'] . '</p>';
  }
  
  $Result = '';
  foreach($Items as $id) {
   $GLOBALS['post'] = &TPost::Instance($id);
   $Result .=  $Template->ParseFile('postexcerpt.tml');
  }
  
  return $Result;
 }
 
 public function PrintNaviPages($url, $page, $count) {
  global  $Options;
  if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
  $result = '<p>';
  $result .= $page== 1 ? '1' : "<a href=\"$Options->url$url\">1</a>";
  $url = rtrim($url, '/');
  for ($i = 2; $i <= $count; $i++) {
   if ($i != $page) {
    $result .= "|<a href=\"$Options->url$url/page/$i/\">$i</a>";
   } else {
    $result .= "|$i";
   }
  }
  
  $result .= "</p>\n";
  return $result;
 }
 
}//class

?>