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
    litepublisher::$urlmap->setexpired($this->idcomments);
    litepublisher::$urlmap->setexpired($this->idpostcomments);
  }
  
  public function request($arg) {
    $result = '';
    if (($arg == 'posts') && ($this->feedburner  != '')) {
      $result .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        if (function_exists('status_header')) status_header( 307 );
        header('Location:$this->feedburner');
        header('HTTP/1.1 307 Temporary Redirect');
        return;
      }
      ?>";
    }elseif (($arg == 'comments') && ($this->feedburnercomments  != '')) {
      $result .= "<?php
      if (!preg_match('/feedburner|feedvalidator/i', \$_SERVER['HTTP_USER_AGENT'])) {
        if (function_exists('status_header')) status_header( 307 );
        header('Location:$this->feedburnercomments');
        header('HTTP/1.1 307 Temporary Redirect');
        return;
      }
      ?>";
    }
    
    $result .= turlmap::xmlheader();
    $this->domrss = new tdomrss;
    switch ($arg) {
      case 'posts':
      $this->getrecentposts();
      break;
      
      case 'comments':
      $this->GetRecentComments();
      break;
      
      case 'categories':
      case 'tags':
      if (!preg_match('/\/(\d*?)\.xml$/', litepublisher::$urlmap->url, $match)) return 404;
      $id = (int) $match[1];
      $tags = $arg == 'categories' ? tcategories::instance() : ttags::instance();
      if (!$tags->itemexists($id)) return 404;
      $this->gettagrss($tags, $id);
      break;
      
      default:
      if (!preg_match('/\/(\d*?)\.xml$/', litepublisher::$urlmap->url, $match)) return 404;
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
  
  public function getrecentposts() {
    $this->domrss->CreateRoot(litepublisher::$site->url. '/rss.xml', litepublisher::$site->name);
    $posts = tposts::instance();
    $this->getrssposts($posts->getrecent(litepublisher::$options->perpage));
  }
  
  public function getrssposts(array $list) {
    foreach ($list as $id ) {
      $post = tpost::instance($id);
      $this->AddRSSPost($post);
    }
  }
  
  public function gettagrss(tcommontags $tags, $id) {
    $this->domrss->CreateRoot(litepublisher::$site->url. litepublisher::$urlmap->url, $tags->getvalue($id, 'title'));
    
    $items = $tags->itemsposts->getposts($id);
    $posts = litepublisher::$classes->posts;
    $items = $posts->stripdrafts($items);
    $items = $posts->sortbyposted($items);
    $this->getrssposts(array_slice($items, 0, litepublisher::$options->perpage));
  }
  
  public function GetRecentComments() {
    $this->domrss->CreateRoot(litepublisher::$site->url . '/comments.xml', tlocal::$data['comment']['onrecent'] . ' '. litepublisher::$site->name);
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent(litepublisher::$options->perpage);
    $title = tlocal::$data['comment']['onpost'] . ' ';
    $comment = new tarray2prop();
    foreach ($recent  as $item) {
      $comment->array = $item;
      $this->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
  public function getholdcomments($url, $count) {
    $result = turlmap::xmlheader();
    $this->dogetholdcomments($url, $count);
    $result .= $this->domrss->GetStripedXML();
    return $result;
  }
  
  private function dogetholdcomments($url, $count) {
    $this->domrss->CreateRoot(litepublisher::$site->url . $url, tlocal::$data['comment']['onrecent'] . ' '. litepublisher::$site->name);
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent($count, 'hold');
    $title = tlocal::$data['comment']['onpost'] . ' ';
    $comment = new tarray2prop();
    foreach ($recent  as $item) {
      $comment->array = $item;
      $this->AddRSSComment($comment, $title . $comment->title);
    }
  }
  
  public function GetRSSPostComments($idpost) {
    $post = tpost::instance($idpost);
    $lang = tlocal::instance('comment');
    $title = $lang->from . ' ';
    $this->domrss->CreateRoot($post->rsscomments, "$lang->onpost $post->title");
    $comments = tcomments::instance($idpost);
    $comment = new tarray2prop();
    if (dbversion) {
      $recent = $comments->select("post = $idpost and status = 'approved'",
      "order by $comments->thistable.posted desc limit ". litepublisher::$options->perpage);
      
      foreach ($recent  as $id) {
        $comment->array = $comments->getitem($id);
        $comment->posturl = $post->url;
        $comment->title = $post->title;
        $this->AddRSSComment($comment, $title . $comment->name);
      }
    } else {
      $from =max(0, count($comments->items) - litepublisher::$options->perpage);
      $items = array_slice(array_keys($comments->items), $from, litepublisher::$options->perpage);
      $items = array_reverse($items);
      $comusers = tcomusers::instance($idpost);
      foreach ($items as $id) {
        $item = $comments->items[$id];
        $item['id'] = $id;
        $author = $comusers->items[$item['author']];
        $item['name'] = $author['name'];
        $item['email'] = $author['email'];
        $item['url'] = $author['url'];
        
        $comment->array = $item;
        $comment->posturl = $post->url;
        $comment->title = $post->title;
        $this->AddRSSComment($comment, $title . $comment->name);
      }
    }
    
    
  }
  
  public function AddRSSPost(tpost $post) {
    $item = $this->domrss->AddItem();
    tnode::addvalue($item, 'title', $post->title);
    tnode::addvalue($item, 'link', $post->link);
    tnode::addvalue($item, 'comments', $post->link . '#comments');
    tnode::addvalue($item, 'pubDate', $post->pubdate);
    
    $guid  = tnode::addvalue($item, 'guid', $post->link);
    tnode::attr($guid, 'isPermaLink', 'true');
    if (class_exists   ('tprofile')) {
      $profile = tprofile::instance();
      tnode::addvalue($item, 'dc:creator', $profile->nick);
    } else {
      tnode::addvalue($item, 'dc:creator', 'admin');
    }
    
    $categories = tcategories::instance();
    $names = $categories->GetNames($post->categories);
    foreach ($names as $name) {
      if (empty($name)) continue;
      tnode::addcdata($item, 'category', $name);
    }
    
    $tags = ttags::instance();
    $names = $tags->GetNames($post->tags);
    foreach ($names as $name) {
      if (empty($name)) continue;
      tnode::addcdata($item, 'category', $name);
    }
    
    $content = '';
    $this->callevent('beforepost', array($post->id, &$content));
    if ($this->template == '') {
      $content .= $post->replacemore($post->rss, true);
    } else {
      eval('$content .= "'. $this->template . '";');
    }
    $this->callevent('afterpost', array($post->id, &$content));
    
    tnode::addcdata($item, 'content:encoded', $content);
    tnode::addcdata($item, 'description', strip_tags($content));
    tnode::addvalue($item, 'wfw:commentRss', $post->rsscomments);
    
    if (count($post->files) > 0) {
      $files = tfiles::instance();
      $files->loaditems($post->files);
      foreach ($post->files as $idfile) {
        $file = $files->getitem($idfile);
        $enclosure = tnode::add($item, 'enclosure');
        tnode::attr($enclosure , 'url', litepublisher::$site->files . '/files/' . $file['filename']);
        tnode::attr($enclosure , 'length', $file['size']);
        tnode::attr($enclosure , 'type', $file['mime']);
      }
      $post->onrssitem($item);
    }
    
  }
  
  public function AddRSSComment($comment, $title) {
    $link = litepublisher::$site->url . $comment->posturl . '#comment-' . $comment->id;
    $date = is_int($comment->posted) ? $comment->posted : strtotime($comment->posted);
    $item = $this->domrss->AddItem();
    tnode::addvalue($item, 'title', $title);
    tnode::addvalue($item, 'link', $link);
    tnode::addvalue($item, 'dc:creator', $comment->name);
    tnode::addvalue($item, 'pubDate', date('r', $date));
    tnode::addvalue($item, 'guid', $link);
    tnode::addcdata($item, 'description', strip_tags($comment->content));
    tnode::addcdata($item, 'content:encoded', $comment->content);
  }
  
  public function SetFeedburnerLinks($rss, $comments) {
    if (($this->feedburner != $rss) || ($this->feedburnercomments != $comments)) {
      $this->feedburner= $rss;
      $this->feedburnercomments = $comments;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
}//class

?>