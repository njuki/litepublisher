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
    $a = array();
    $comment = new tarray2prop($a);
    foreach ($recent  as $item) {
      $comment->array = $item;
      $this->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
  public function GetRSSPostComments($idpost) {
    global $options;
    $post = tpost::instance($idpost);
    $lang = tlocal::instance('comment');
    $title = $lang->from . ' ';
    $this->domrss->CreateRoot($post->rsscomments, "$lang->onpost $post->title");
    $comments = tcomments::instance($idpost);
    $a = array();
    $comment = new tarray2prop($a);
    if (dbversion) {
      $recent = $comments->getitems("post = $idpost and status = 'approved'
      order by $comments->thistable.posted desc limit $options->postsperpage");
      
    } else {
      $from =max(0, count($comments->items) - $options->postsperpage);
      $items = array_slice(array_keys($comments->items), $from, $options->postsperpage);
      $recent = array();
      $comusers = tcomusers::instance($idpost);
      foreach ($items as $id) {
        $item = $comments->items[$id];
        $item['id'] = $id;
        $item['posturl'] =
        
        $author = $comusers->items[$item['author']];
        $item['name'] = $author['name'];
        $item['email'] = $author['email'];
        $item['url'] = $author['url'];
        array_push($recent, $item);
      }
    }
    
    foreach ($recent  as $item) {
      $comment->array = $item;
      $comment->posturl = $post->url;
      $comment->title = $post->title;
      $this->AddRSSComment($comment, $title . $comment->name);
    }
    
  }
  
  public function AddRSSPost(tpost $post) {
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
    $this->beforepost($post->id, &$content);
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
    
    if (count($post->files) > 0) {
      $files = tfiles::instance();
      $fileitems = $files->getitems($post->files);
      foreach ($fileitems as $file) {
        $enclosure = AddNode($item, 'enclosure');
        AddAttr($enclosure , 'url', $options->files . '/files/' . $file['filename']);
        AddAttr($enclosure , 'length', $file['size']);
        AddAttr($enclosure , 'type', $file['mime']);
      }
    }
    
  }
  
  public function AddRSSComment($comment, $title) {
    global $options;
    $link = "$options->url$comment->posturl#comment-$comment->id";
    $date = is_int($comment->posted) ? $comment->posted : strtotime($comment->posted);
    $item = $this->domrss->AddItem();
    AddNodeValue($item, 'title', $title);
    AddNodeValue($item, 'link', $link);
    AddNodeValue($item, 'dc:creator', $comment->name);
    AddNodeValue($item, 'pubDate', date('r', $date));
    AddNodeValue($item, 'guid', $link);
    AddCData($item, 'description', strip_tags($comment->content));
    AddCData($item, 'content:encoded', $comment->content);
  }
  
  public function SetFeedburnerLinks($rss, $comments) {
    if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
      $this->feedburner= $rss;
      $this->feedburnercomments = $comments;
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    }
  }
  
}//class

?>