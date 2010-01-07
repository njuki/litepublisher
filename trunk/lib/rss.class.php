<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trss extends tevents {
  public $domrss;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'rss';
    $this->addevents('beforepost', 'afterpost');
    $this->data['feedburner'] = '';
    $this->data['feedburnercomments'] = '';
    $this->data['template'] = '';
    $this->data['idcomments'] = 0;
    $this->data['idpostcomments'] = 0;
  }
  
  public function commentschanged($idpost) {
    $urlmap = turlmap::instance();
    $urlmap->setexpired($this->idcomments);
    $urlmap->setexpired($this->idpostcomments);
  }
  
  public function request($arg) {
    global $options, $urlmap;
    $result = "<?php\n";
    if (($arg == 'posts') && ($this->feedburner  != '')) {
      $result .= "if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        if (function_exists('status_header')) status_header( 307 );
        header('Location:$this->feedburner');
        header('HTTP/1.1 307 Temporary Redirect');
        return;
      }
      ";
    }elseif (($arg == 'comments') && ($this->feedburnercomments  != '')) {
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
    
    switch ($arg) {
      case 'posts':
      $this->GetRSSRecentPosts();
      break;
      
      case 'comments':
      $this->GetRecentComments();
      break;
      
      default:
      if (!preg_match('/\/(\d*?)\.xml$/', $urlmap->url, $match)) return 404;
      $idpost = (int) $match[1];
      $posts = tposts::instance();
      if (!$posts->itemexists($idpost)) return 404;
      $post = tpost::instance($idpost);
      if ($post->status != 'published') return 404;
      $this->GetRSSPostComments($idpost);
    }
    
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  public function GetRSSRecentPosts() {
    global $options;
    $this->domrss->CreateRoot($options->url. '/rss/', $options->name);
    $posts = tposts::instance();
    $list = $posts->getrecent($options->postsperpage);
    foreach ($list as $id ) {
      $post = tpost::instance($id);
      $this->AddRSSPost($post);
    }
    
  }
  
  public function GetRecentComments() {
    global $options;
    $this->domrss->CreateRoot($options->url . '/comments.xml', tlocal::$data['comment']['onrecent'] . ' '. $options->name);
$manager = tcommentmanager::instance();    
$recent = $manager->getrecent($options->postsperpage);
        $title = tlocal::$data['comment']['onpost'] . ' ';
$comment = new tarray2prop();
foreach ($recent  as $item) {
$comment->array = $item;
            $this->AddRSSComment($comment, $title . $comment->title);
          }
}
    
    public function GetRSSPostComments($idpost) {
      global $options;
      $post = tpost::instance($idpost);
      $lang = tlocal::instance('comment');
      $this->domrss->CreateRoot($post->rsscomments, "$lang->onpost $post->title");
      $count = $options->postsperpage;
      $comment = new TComment($post->comments);
      $items = &$post->comments->items;
      $title = tlocal::$data['comment']['from'] . ' ';
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
      
      $profile = tprofile::instance();
      AddNodeValue($item, 'dc:creator', $profile->nick);
      
      $categories = tcategories::instance();
      $names = $categories->GetNames($post->categories);
      foreach ($names as $name) {
        if (empty($name)) continue;
        AddCData($item, 'category', $name);
      }
      
      $tags = ttags::instance();
      $names = $tags->GetNames($post->tags);
      foreach ($names as $name) {
        if (empty($name)) continue;
        AddCData($item, 'category', $name);
      }
      
      $content = '';
      $this->beforepost($post->id, &$$content);
      if ($this->template == '') {
        $content .=$post->rss;
        $content .= $post->morelink;
      } else {
        eval('$content .= "'. $this->template . '";');
      }
      $this->afterpost($post->id, &$content);
      
      AddCData($item, 'description', strip_tags($content));
      AddCData($item, 'content:encoded', $content);
      AddNodeValue($item, 'wfw:commentRss', $post->rsscomments);
    }
    
    public function AddRSSComment($comment, $title) {
      global $options;
$link = "$options->url$comment->posturl#comment-$comment->id";
      $item = $this->domrss->AddItem();
      AddNodeValue($item, 'title', $title);
      AddNodeValue($item, 'link', $link);
      AddNodeValue($item, 'dc:creator', $comment->name);
      AddNodeValue($item, 'pubDate', date('r', $comment->date));
      AddNodeValue($item, 'guid', $link);
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