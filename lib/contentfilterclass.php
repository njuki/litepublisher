<?php

class TContentFilter extends TEventClass {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'contentfilter';
  $this->AddEvents('OnComment', 'OnPost', 'OnRSS', 'OnExcerpt');
  $this->Data['automore'] = false;
  $this->Data['automorelength'] = 150;
 }
 
 public function GetCommentContent($content) {
  $s = $this->OnComment($content);
  if ($s != '') $content  = $s;
  $result = trim(strip_tags($content));
  $result = htmlspecialchars($result);
  $result = str_replace("\r\n", "\n", $result);
  $result = str_replace("\r", "\n", $result);
  $result = str_replace("\n\n", "</p><p>", $result);
  $result = str_replace("\n", "<br />\n", $result);
  
  return $result;
 }
 
 public function SetPostContent(&$post, $s) {
  $s = $this->FilterInternalLinks($s);
  if ( preg_match('/<!--more(.*?)?-->/', $s, $matches)  ||
  preg_match('/\[more(.*?)?\]/', $s, $matches)  ||
  preg_match('/\[cut(.*?)?\]/', $s, $matches)
  ) {
   $parts = explode($matches[0], $s, 2);
   $post->excerpt = $this->GetPostContent($parts[0]);
   $post->OutputContent = $post->excerpt . $this->GetPostContent($parts[1]);
   $post->rss =  str_replace(']]>', ']]]]><![CDATA[>',$post->excerpt);
   $post->moretitle =  self::NormalizeMoreTitle($matches[1]);
   if ($post->moretitle == '')  $post->moretitle = TLocal::$data['post']['more'];
  } else {
   if ($this->automore) {
    $post->OutputContent = $this->GetPostContent($s);
    $post->excerpt = self::GetExcerpt($s, $this->automorelength);
    $post->rss =  str_replace(']]>', ']]]]><![CDATA[>',$post->excerpt);
    $post->moretitle = TLocal::$data['post']['more'];
   } else {
    $post->excerpt = $this->GetPostContent($s);
    $post->OutputContent = $post->excerpt;
    $post->rss =  str_replace(']]>', ']]]]><![CDATA[>',$post->excerpt);
    $post->moretitle =  '';
   }
  }
  $post->description = self::GetExcerpt($post->excerpt, 80);
  $this->DoFilterEvents($post);
 }
 
 private function DoFilterEvents(&$post) {
  $s = $this->OnPost(    $post->OutputContent);
  if ($s != '') $post->OutputContent =  $s;
  
  $s = $this->OnExcerpt($post->excerpt);
  if ($s != '') $post->excerpt = $s;
  
  $s = $this->OnRSS($post->rss);
  if ($s != '') $post->rss = $s;
 }
 
 public static function NormalizeMoreTitle($s) {
  $s = trim($s);
  $s = preg_replace('/\0+/', '', $s);
  $s = preg_replace('/(\\\\0)+/', '', $s);
  $s = strip_tags($s);
  return trim($s);
 }
 
 public function GetPostContent($content) {
  $result = trim($content);
  $result = self::ReplaceCode($result);
  $result = str_replace("\r\n", "\n", $result);
  $result = str_replace("\r", "\n", $result);
  //послетега  до конца строки удаляются пробеллы
  $result = preg_replace('/\>(\s*?)?\n/',">\n", $result);
  //ставятся два праграфа если небыло тегов
  $result = preg_replace('/(?<!\>)\n\n(?!\s*\<)/im', "</p>\n<p>",$result);
  //закрывается параграф перед тегом через строку
  $result = preg_replace('/(?<!\>)(\s*?)?\n\n(\s*\<)/im', "</p>\n<",$result);
  //через строку открывается параграф после закрытия тега
  $result = preg_replace('/(\>)(\s*)\n\n(?!\s*\<)/im', ">\n<p>",$result);
  //переводыстроки если нет в конце тегов
  $result = preg_replace('/(?<!\>)\n(?!\s*\<)/im', "<br />\n", $result);
  
  return "<p>" . $result . "</p>\n";
 }
 
 public static function ReplaceCode($s) {
  return preg_replace_callback('/<code>(.*?)<\/code>/ims', 'TContentFilter::CallbackReplaceCode', $s);
 }
 
 public static function CallbackReplaceCode($found) {
  $code = str_replace(' ', '&nbsp;', htmlspecialchars($found[1]));
  return "<code>$code</code>";
 }
 
 public static function GetExcerpt($content, $len) {
  $result = strip_tags($content);
  if (strlen($result) <= $len) return $result;
  $chars = "\n ,.;!?:(";
  $p = strlen($result);
  for ($i = strlen($chars) - 1; $i >= 0; $i--) {
  if($pos = strpos($result, $chars{$i}, $len)) {
    $p = min($p, $pos + 1);
   }
  }
  return substr($result, 0, $p);
 }
 
 public static function ValidateEmail($email) {
 return  preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
 }
 
 public function FilterInternalLinks($s) {
  global $Options;
  if (strpos($s, '[bloglink]')) {
   $bloglink = "<a href=\"$Options->url$Options->home\">$Options->name</a>";
   $s = str_replace('[bloglink]', $bloglink, $s);
  }
  
  if (strpos($s, '[prevpost]')) {
   $posts = &TPosts::Instance();
   $last = $posts->GetRecent(1);
   $post = &TPost::Instance($last[0]);
   $link = "<a href=\"$Options->url$post->url\">$post->title</a>";
   $s = str_replace('[lastpost]', $link, $s);
  }
  
  if (strpos($s, '[file]')) {
   $files = &TFiles::Instance();
   $s = str_replace('[file]', $files->Getlink($files->lastid), $s);
  }
  
  return $s;
 }
 
}
?>