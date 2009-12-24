<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trss extends tevents {
  public $domrss;
  
  public static function &instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'rss';
    $this->addevents('beforepost', 'afterpost');
    $this->data['feedburner'] = '';
    $this->data['feedburnercomments'] = '';
    $this->data['template'] = '';
    $this->data['idurlcomments'] = 0;
  }
  
  public function commentschanged($postid) {
    $urlmap = turlmap::instance();
    $urlmap->setexpired($this->idurlcomments);
  }
  
  public function request($args) {
    global $options, $urlmap;
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
    @header('X-Pingback: $options->url/rpc.xml');
    echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>';
    ?>";
    
    $this->domrss = new Tdomrss;
    
    switch ($args) {
      case 'posts':
      $this->GetRSSRecentPosts();
      break;
      
      case null:
      $this->GetRecentComments();
      break;
      
      default:
      $postid = (int) $args;
      $posts = tposts::instance();
      if (!$posts->itemexists($postid)) return 404;
      $post = TPost::instance($postid);
      if ($post->status != 'published') return 404;
      $this->GetRSSPostComments($postid);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  public function GetRSSRecentPosts() {
    global $options;
    $this->domrss->CreateRoot($options->url. '/rss/', $options->name);
    $posts = &TPosts::instance();
    $list = $posts->GetRecent($options->postsperpage);
    foreach ($list as $id ) {
      $post = TPost::instance($id);
      $this->AddRSSPost($post);
    }
    
  }
  
  public function GetRecentComments() {
    global $options;
    $this->domrss->CreateRoot($options->url . '/comments/', TLocal::$data['comment']['onrecent'] . ' '. $options->name);
    
    $count = $options->postsperpage;
    $CommentManager = TCommentManager::instance();
    if ($item = end($CommentManager->items)) {
      $comment = &new TComment();
      $title = TLocal::$data['comment']['onpost'] . ' ';
      do {
        $id = key($CommentManager->items);
        if (!isset($item['status']) && !isset($item['type'])) {
          $post = TPost::instance($item['pid']);
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
    global $options;
    $post = TPost::instance($postid);
    $lang = TLocal::instance('comment');
    $this->domrss->CreateRoot($post->rsslink, "$lang->onpost $post->title");
    $count = $options->postsperpage;
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
    global $options;
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $post->title);
    AddNodeValue($item, 'link', $post->link);
    AddNodeValue($item, 'comments', $post->link . '#comments');
    AddNodeValue($item, 'pubDate', $post->pubdate);
    
    $guid  = AddNodeValue($item, 'guid', $post->link);
    AddAttr($guid, 'isPermaLink', 'false');
    
    $profile = &TProfile::instance();
    AddNodeValue($item, 'dc:creator', $profile->nick);
    
    $categories = TCategories::instance();
    $names = $categories->GetNames($post->categories);
    foreach ($names as $name) {
      if (empty($name)) continue;
      AddCData($item, 'category', $name);
    }
    
    $tags = TTags::instance();
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
    global $options;
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $title);
    AddNodeValue($item, 'link', "$options->url$posturl#comment-$comment->id");
    AddNodeValue($item, 'dc:creator', $comment->name);
    AddNodeValue($item, 'pubDate', date('r', $comment->date));
    AddNodeValue($item, 'guid', "$options->url$posturl#comment-$comment->id");
    AddCData($item, 'description', strip_tags($comment->content));
    AddCData($item, 'content:encoded', $comment->content);
  }
  
  public function SetFeedburnerLinks($rss, $comments) {
    if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
      $this->feedburner= $rss;
      $this->feedburnercomments = $comments;
      $this->Save();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    }
  }
  
}//class

?>