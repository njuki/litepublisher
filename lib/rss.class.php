<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class trss extends tevents {
  public $domrss;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'rss';
    $this->Data['feedburner'] = '';
    $this->Data['feedburnercomments'] = '';
    $this->Data['template'] = '';
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
    @header('X-Pingback: $Options->url/rpc.xml');
    echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>';
    ?>";
    
    $this->domrss = new Tdomrss;
    
    switch ($args) {
      case 'posts':
      $this->GetRSSRecentPosts();
      break;
      
      case null:
      $Urlmap->page = 0;
      $this->GetRecentComments();
      break;
      
      default:
      $postid = (int) $args;
      $posts = &TPosts::Instance();
      if (!isset($posts->archives[$postid])) return 404;
      $Urlmap->page = $postid;
      $this->GetRSSPostComments($postid);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  public function GetRSSRecentPosts() {
    global $Options;
    $this->domrss->CreateRoot($Options->url. '/rss/', $Options->name);
    $posts = &TPosts::Instance();
    $list = $posts->GetRecent($Options->postsperpage);
    foreach ($list as $id ) {
      $post = TPost::Instance($id);
      $this->AddRSSPost($post);
    }
    
  }
  
  public function GetRecentComments() {
    global $Options;
    $this->domrss->CreateRoot($Options->url . '/comments/', TLocal::$data['comment']['onrecent'] . ' '. $Options->name);
    
    $count = $Options->postsperpage;
    $CommentManager = TCommentManager::Instance();
    if ($item = end($CommentManager->items)) {
      $comment = &new TComment();
      $title = TLocal::$data['comment']['onpost'] . ' ';
      do {
        $id = key($CommentManager->items);
        if (!isset($item['status']) && !isset($item['type'])) {
          $post = TPost::Instance($item['pid']);
          if ($post->status != 'published') continue;
          $count--;
          $comment->Owner = &$post->comments;
          $comment->id = $id;
          $this->AddRSSComment($comment, $post->url, $title . $post->title);
        }
      } while (($count > 0) && ($item = prev($CommentManager->items)));
    }
    
  }
  
  public function GetRSSPostComments($postid) {
    global $Options;
    $post = TPost::Instance($postid);
    $lang = TLocal::Instance('comment');
    $this->domrss->CreateRoot($post->rsslink, "$lang->onpost $post->title");
    $count = $Options->postsperpage;
    $comment = new TComment($post->comments);
    $items = &$post->comments->items;
    $title = TLocal::$data['comment']['from'] . ' ';
    foreach ($items as $id => $item) {
      if (($item['status'] == 'approved') && ($item['type'] == '')) {
        $count--;
        $comment->id = $id;
        $this->AddRSSComment($comment, $post->url, $title . $comment->name);
      }
      if ($count ==0) break;
    }
    
  }
  
  public function AddRSSPost(&$post) {
    global $Options;
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $post->title);
    AddNodeValue($item, 'link', $post->link);
    AddNodeValue($item, 'comments', $post->link . '#comments');
    AddNodeValue($item, 'pubDate', $post->pubdate);
    
    $guid  = AddNodeValue($item, 'guid', $post->link);
    AddAttr($guid, 'isPermaLink', 'false');
    
    $profile = &TProfile::Instance();
    AddNodeValue($item, 'dc:creator', $profile->nick);
    
    $categories = TCategories::Instance();
    $names = $categories->GetNames($post->categories);
    foreach ($names as $name) {
      if (empty($name)) continue;
      AddCData($item, 'category', $name);
    }
    
    $tags = TTags::Instance();
    $names = $tags->GetNames($post->tags);
    foreach ($names as $name) {
      if (empty($name)) continue;
      AddCData($item, 'category', $name);
    }
    
    $content = $this->BeforePostContent($post->id);
    if ($this->template == '') {
      $content .=$post->rss;
      $content .= $post->morelink;
    } else {
      eval('$content .= "'. $this->template . '";');
    }
    $content .= $this->AfterPostContent($post->id);
    
    AddCData($item, 'description', strip_tags($content));
    AddCData($item, 'content:encoded', $content);
    AddNodeValue($item, 'wfw:commentRss', $post->rsslink);
  }
  
  public function AddRSSComment(&$comment, $posturl, $title) {
    global $Options;
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $title);
    AddNodeValue($item, 'link', "$Options->url$posturl#comment-$comment->id");
    AddNodeValue($item, 'dc:creator', $comment->name);
    AddNodeValue($item, 'pubDate', date('r', $comment->date));
    AddNodeValue($item, 'guid', "$Options->url$posturl#comment-$comment->id");
    AddCData($item, 'description', strip_tags($comment->content));
    AddCData($item, 'content:encoded', $comment->content);
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