<?php

class TRSS extends TEventClass {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'rss';
  $this->Data['feedburner'] = '';
  $this->Data['feedburnercomments'] = '';
  $this->AddEvents('BeforePostContent', 'AfterPostContent');
 }
 
 public function CommentsChanged($postid) {
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->SubNodeExpired('comments', $postid);
  $Urlmap->SubNodeExpired('comments', 0);
 }
 
 public function Request($args) {
  global $Options, $Urlmap;
  $result = "<?php\n";
  if (($args == 'posts') && ($this->feedburner  != '')) {
   $result .= "if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
    if (function_exists('status_header')) status_header( 307 );
    header('Location:$this->feedburner');
    header('HTTP/1.1 307 Temporary Redirect');
    return;
   }
   ";
  }elseif (($args == null) && ($this->feedburnercomments  != '')) {
   $result .= "if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
    if (function_exists('status_header')) status_header( 307 );
    header('Location:$this->feedburnercomments');
    header('HTTP/1.1 307 Temporary Redirect');
    return;
   }
   ";
  }
  
  $result .= "  @header('Content-Type: text/xml; charset=utf-8');
  @ header('Last-Modified: " . date('r') ."');
  @header('X-Pingback: $Options->pingurl');
  echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>';
  ?>";
  
  switch ($args) {
   case 'posts':
   $result .= $this->GetRSSRecentPosts();
   break;
   case null:
   $Urlmap->pagenumber = 0;
   $result .= $this->GetRecentComments();
   break;
   
   default:
   $postid = (int) $args;
   $posts = &TPosts::Instance();
   if (!isset($posts->archives[$postid])) return 404;
   $Urlmap->pagenumber = $postid;
   $result .= $this->GetRSSPostComments($postid);
  }
  return $result;
 }
 
 public function GetRSSRecentPosts() {
  global $Options;
  $posts = &TPosts::Instance();
  $list = $posts->GetRecent($Options->postsperpage);
  $result = $this->GetRSSHeader('');
  foreach ($list as $id ) {
   $post = &TPost::Instance($id);
   $result .= $this->GetRSSPost($post);
  }
  
  $result .= $this->GetRSSFooter();
  return $result;
 }
 
 public function GetRecentComments() {
  global $Options;
  $count = $Options->postsperpage;
  $result = $this->GetRSSHeader(TLocal::$data['comment']['onrecent']);
  $CommentManager = &TCommentManager::Instance();
  if ($item = end($CommentManager->items)) {
   $comment = &new TComment();
   $title = TLocal::$data['comment']['onpost'] . ' ';
   do {
    $id = key($CommentManager->items);
    if (!isset($item['status']) && !isset($item['type'])) {
     $post = &TPost::Instance($item['pid']);
     if ($post->status != 'published') continue;
     $count--;
     $comment->Owner = &$post->comments;
     $comment->id = $id;
     $result .= $this->GetRSSComment($comment, $post->url, $title . $post->title);
    }
   } while (($count > 0) && ($item = prev($CommentManager->items)));
  }
  
  $result .= $this->GetRSSFooter();
  return $result;
 }
 
 public function GetRSSPostComments($postid) {
  global $Options;
  $count = $Options->postsperpage;
  $post = &TPost::Instance($postid);
  $result = $this->GetRSSHeader(TLocal::$data['comment']['onpost'] . ' ' . $post->title);
  $comment = &new TComment();
  $comment->Owner = &$post->comments;
  $items = &$post->comments->items;
  $title = TLocal::$data['comment']['from'] . ' ';
  foreach ($items as $id => $item) {
   if (($item['status'] == 'approved') && ($item['type'] == '')) {
    $count--;
    $comment->id = $id;
    $result .= $this->GetRSSComment($comment, $post->url, $title . $comment->name);
   }
   if ($count ==0) break;
  }
  
  $result .= $this->GetRSSFooter();
  return $result;
 }
 
 public function GetRSSHeader($title) {
  global $Options;
  $pubdate = gmdate("D, d M Y H:i:s") . " +0000";
  return "\n<!-- generator=\"Lite Publisher/$Options->version version\" -->
  <rss version=\"2.0\"
  xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"
  xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\"
  xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
  xmlns:atom=\"http://www.w3.org/2005/Atom\">
  <channel>
  <atom:link href=\"$Options->rss\" rel=\"self\" type=\"application/rss+xml\" />
  <title>$title$Options->name</title>
  <link>$Options->rss</link>
  <description>$Options->description</description>
  <pubDate>$pubdate</pubDate>
  <generator>http://litepublisher.com/?version=$Options->version</generator>
  <language>en</language>
  ";
 }
 
 public function GetRSSPost(&$post) {
  global $Options;
  $categories = &TCategories::Instance();
  $names = $categories->GetNames($post->categories);
  $RSSCategories= '';
  foreach ($names as $name) {
   if (empty($name)) continue;
   $RSSCategories.= "<category><![CDATA[$name]]></category> \n";
  }
  
  $tags = &TTags::Instance();
  $names = $tags->GetNames($post->tags);
  foreach ($names as $name) {
   if (empty($name)) continue;
   $RSSCategories.= "<category><![CDATA[$name]]></category> \n";
  }
  
  $posturl = $Options->url . $post->url;
  $pubdate = gmdate("D, d M Y H:i:s", $post->date - gmt_offset) . " +0000";
  $CommentRSS = $Options->url . "/comments/$post->id/";
  
  $profile = &TProfile::Instance();
  $content = $this->BeforePostContent($post->id);
  $content .=$post->rss;
  $content .= $post->morelink;
  $content .= $this->AfterPostContent($post->id);
  
  return "<item>
  <title>$post->title</title>
  <link>$posturl</link>
  <comments>$posturl#comments</comments>
  <pubDate>$pubdate</pubDate>
  <dc:creator>$profile->nick</dc:creator>
  $RSSCategories
  <guid isPermaLink=\"false\">$posturl</guid>
  <description><![CDATA[". strip_tags($content) . "]]></description>
  <content:encoded><![CDATA[$content]]></content:encoded>
  <wfw:commentRss>$CommentRSS</wfw:commentRss>
  </item>
  ";
 }
 
 public function GetRSSFooter() {
  return '</channel>
  </rss>';
 }
 
 public function GetRSSComment(&$comment, $posturl, $title) {
  global $Options;
  $pubdate = gmdate("D, d M Y H:i:s", $comment->date - gmt_offset) . " +0000";
  
  return "<item>
  <title>$title</title>
  <link>$Options->url$posturl#comment-$comment->id</link>
  <dc:creator>$comment->name</dc:creator>
  <pubDate>$pubdate</pubDate>
  <guid>$Options->url$posturl#comment-$comment->id</guid>
  <description><![CDATA[". strip_tags($comment->content) . "]]></description>
  <content:encoded><![CDATA[$comment->content]]></content:encoded>
  </item>
  ";
 }
 
 public function SetFeedburnerLinks($rss, $comments) {
  if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
   $this->feedburner= $rss;
   $this->feedburnercomments = $comments;
   $this->Save();
   $urlmap = &TUrlmap::Instance();
   $urlmap->ClearCache();
  }
 }
 
}//class

?>