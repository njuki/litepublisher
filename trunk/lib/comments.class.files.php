<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcomments extends titems {
  public $pid;
  private $rawitems;
  private $holditems;
  private static $instances;
  
  public static function instance($pid) {
    $pid = (int) $pid;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
    $self = litepublisher::$classes->newinstance(__class__);
    self::$instances[$pid]  = $self;
    $self->pid = $pid;
    $self->load();
    return $self;
  }
  
  protected function create() {
    parent::create();
    $this->addevents('edited');
  }
  
  public function getcomment($id) {
    $result = new tcomment($this);
    $result->id = $id;
    if (!isset($this->items[$id]) && isset($this->hold->items[$id])) $result->owner = $this->hold;
    return $result;
  }
  
  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments';
  }
  
  public function getraw() {
    if (!isset($this->rawitems)) {
      $this->rawitems = new trawcomments($this);
    }
    return $this->rawitems;
  }
  
  public function gethold() {
    if (!isset($this->holditems)) {
      $this->holditems = new tholdcomments($this);
    }
    return $this->holditems;
  }
  
  public function getapprovedcount() {
    return count($this->items);
  }
  
  public function add($author, $content, $status, $ip) {
    $filter = tcontentfilter::instance();
    $item  = array(
    'author' => $author,
    'posted' => time(),
    'content' => $filter->filtercomment($content)
    );
    
    if ($status == 'approved') {
      $this->items[++$this->autoid] = $item;
    } else {
      $this->hold->items[++$this->autoid] =  $item;
      $this->hold->save();
    }
    $this->save();
    
    $this->raw->add($this->autoid, $content, $ip);
    $this->added($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $author, $content) {
    if (isset($this->items[$id])) {
      $item = &$this->items[$id];
      $approved = true;
    } elseif (isset($this->hold->items[$id])) {
      $item = &$this->hold->items[$id];
      $approved = false;
    } else {
      return false;
    }
    
    $filter = tcontentfilter::instance();
    
    $item['author'] = $author;
    $item['content'] = $filter->filtercomment($content);
    
    if ($approved) {
      $this->save();
    } else {
      $this->hold->save();
    }
    
    $this->raw->items[$id]['content'] = $content;
    $this->raw->save();
    $this->edited($id);
    return true;
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      $author = $this->items[$id]['author'];
      unset($this->items[$id]);
      $this->save();
    } else {
      if (!isset($this->hold->items[$id])) return false;
      $author = $this->hold->items[$id]['author'];
      unset($this->hold->items[$id]);
      $this->hold->save();
    }
    
    $this->raw->delete($id);
    $this->deleteauthor($author);
    $this->deleted($id);
    return true;
  }
  
  public function deleteauthor($author) {
    foreach ($this->items as $id => $item) {
      if ($author == $item['author'])  return;
    }
    
    foreach ($this->hold->items as $id => $item) {
      if ($author == $item['author'])  return;
    }
    
    //автора не нашли
    $comusers = tcomusers::instance($this->pid);
    $comusers->delete($author);
  }
  
  public function setstatus($id, $status) {
    if ($status == 'approved') {
      return $this->approve($id);
    } else {
      return $this->sethold($id);
    }
  }
  
  public function sethold($id) {
    if (!isset($this->items[$id]))  return false;
    $item = $this->items[$id];
    unset($this->items[$id]);
    $this->save();
    $this->hold->items[$id] = $item;
    $this->hold->save();
    return true;
  }
  
  public function approve($id) {
    if (!isset($this->hold->items[$id]))  return false;
    $this->items[$id] = $this->hold->items[$id];
    $this->save();
    unset($this->hold->items[$id]);
    $this->hold->save();
    return true;
  }
  
  /*
  public function sort() {
    $Result[$id] = $item['posted'];
    asort($Result);
    return  array_keys($Result);
  }
  
  public function insert($author, $content, $ip, $posted, $status) {
    $filter = tcontentfilter::instance();
    $item  = array(
    'author' => $author,
    'posted' => $posted,
    'content' => $filter->filtercomment($content)
    );
    
    if ($status == 'approved') {
      $this->items[++$this->autoid] = $item;
    } else {
      $this->hold->items[++$this->autoid] =  $item;
      $this->hold->save();
    }
    $this->save();
    
    $this->raw->add($this->autoid, $content, $ip);
    return $this->autoid;
  }
  
  */
  
  public function getholdcontent($idauthor) {
    if (litepublisher::$options->admincookie) return '';
    return $this->hold->dogetcontent(true, $idauthor);
  }
  
  public function getmoderator() {
    if (!litepublisher::$options->admincookie) return false;
    if (litepublisher::$options->group == 'admin') return true;
    $groups = tusergroups::instance();
    return $groups->hasrigt(litepublisher::$options->group, 'moderator');
  }
  
  public function getcontent() {
    $result = $this->dogetcontent(false, 0);
    if (!$this->moderator) return $result;
    $theme = ttheme::instance();
    tlocal::loadlang('admin');
    $lang = tlocal::instance('comment');
    $post = tpost::instance($this->pid);
    if ($post->commentpages == litepublisher::$urlmap->page) {
      $result .= $this->hold->dogetcontent(true, 0);
    } else {
      //add empty list of hold comments
      $args = targs::instance();
      $args->comment = '';
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'], $args);
    }
    $args = targs::instance();
    $args->comments = $result;
    return $theme->parsearg($theme->templates['content.post.templatecomments.moderateform'], $args);
  }
  
  public function dogetcontent($hold, $idauthor) {
    $result = '';
    $post = tpost::instance($this->pid);
    $from = 0;
    $items = array_keys($this->items);
    if (!$hold) {
      if (litepublisher::$options->commentpages ) {
        $from = (litepublisher::$urlmap->page - 1) * litepublisher::$options->commentsperpage;
        $items = array_slice($items, $from, litepublisher::$options->commentsperpage, true);
      }
    }
    
    $ismoder = $this->moderator;
    $theme = ttheme::instance();
    $args = targs::instance();
    if (count($items) > 0) {
      $args->from = $from;
      $comment = new tcomment($this);
      ttheme::$vars['comment'] = $comment;
      if ($hold) $comment->status = 'hold';
      $lang = tlocal::instance('comment');
      
      if ($ismoder) {
        tlocal::loadlang('admin');
        $moderate =$theme->templates['content.post.templatecomments.comments.comment.moderate'];
      } else {
        $moderate = '';
      }
      
      $tmlcomment= $theme->gettag('content.post.templatecomments.comments.comment');;
      $tml = strtr((string) $tmlcomment, array(
      '$moderate' => $moderate,
      '$quotebuttons' => $post->commentsenabled ? $tmlcomment->quotebuttons : ''
      ));
      
      $i = 1;
      $class1 = $tmlcomment->class1;
      $class2 = $tmlcomment->class2;
      foreach ($items as $id) {
        //one method for approved and hold comments
        if (!litepublisher::$options->admincookie && $hold) {
          if ($idauthor != $this->items[$id]['author']) continue;
        }
        $comment->id = $id;
        $args->class = (++$i % 2) == 0 ? $class1 : $class2;
        $result .= $theme->parsearg($tml, $args);
      }
    }//if count
    unset(ttheme::$vars['comment']);
    if (!$ismoder) {
      if ($result == '') return '';
    }
    
    if ($hold) {
      $tml = $theme->templates['content.post.templatecomments.holdcomments'];
    } else {
      $tml = $theme->templates['content.post.templatecomments.comments'];
    }
    
    
    $args->from = $from + 1;
    $args->comment = $result;
    return $theme->parsearg($tml, $args);
  }
  
}//class

class tholdcomments extends tcomments {
  public $owner;
  public $idauthor;
  
  public static function instance($pid) {
    $owner = tcomments::instance($pid);
    return $owner->hold;
  }
  
  public function __construct($owner) {
    $this->owner = $owner;
    parent::__construct();
    $this->pid = $owner->pid;
  }
  
  public function getbasename() {
    return $this->owner->getbasename() . '.hold';
  }
  
  public function delete($id) {
    if (!isset($this->items[$id])) return false;
    $author= $this->items[$id]['author'];
    unset($this->items[$id]);
    $this->save();
    $this->owner->raw->delete($id);
    $this->owner->deleteauthor($author);
    $this->deleted($id);
  }
  
}//class

class trawcomments extends titems {
  public $owner;
  
  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->owner->pid . DIRECTORY_SEPARATOR . 'comments.raw';
  }
  
  public function __construct($owner) {
    $this->owner = $owner;
    parent::__construct();
  }
  
  public function add($id, $content, $ip) {
    $this->items[$id] = array(
    'content' => $content,
    'ip' => $ip
    );
    $this->save();
  }
  
}//class

//wrapper for simple acces to single comment
class TComment {
  public $id;
  public $owner;
  public $status;
  
  public function __construct($owner = null) {
    $this->owner = $owner;
    $this->status = 'approved';
  }
  
  public function __get($name) {
    if (method_exists($this,$get = "get$name")) {
      return  $this->$get();
    }
    return $this->owner->items[$this->id][$name];
  }
  
  public function __set($name, $value) {
    if ($name == 'content') {
      $this->setcontent($value);
    } else {
      $this->owner->items[$this->id][$name] = $value;
    }
  }
  
  public function save() {
    $this->owner->save();
  }
  
  private function setcontent($value) {
    $filter = tcontentfilter::instance();
    $this->owner->items[$this->id]['content'] = $filter->filtercomment($value);
    $this->save();
    $this->owner->raw->items[$this->id]['content'] =  $value;
    $this->owner->raw->save();
  }
  
  private function getauthoritem() {
    $comusers = tcomusers::instance($this->owner->pid);
    return  $comusers->getitem($this->author);
  }
  
  public function getname() {
    return $this->authoritem['name'];
  }
  
  public function getemail() {
    return $this->authoritem['email'];
  }
  
  public function getwebsite() {
    return $this->authoritem['url'];
  }
  
  public function getauthorlink() {
    $idpost = $this->owner->pid;
    $comusers = tcomusers::instance($idpost);
    $item = $comusers->getitem($this->author);
    $name = $item['name'];
    
    if (empty($url)) return $name;
    $manager = tcommentmanager::instance();
    if ($manager->hidelink) return $name;
    $rel = $manager->nofollow ? 'rel="nofollow noindex"' : '';
    if ($manager->redir) {
      return "<a $rel href=\"" . litepublisher::$site->url . "/comusers.htm" . litepublisher::$site->q . "id=$this->author&post=$idpost\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
  }
  
  public function getdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
  }
  
  public function getlocalstatus() {
    return tlocal::$data['commentstatus'][$this->status];
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function geturl() {
    $post = tpost::instance($this->owner->pid);
    return "$post->link#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::instance($this->owner->pid);
    return $post->title;
  }
  
  public function getrawcontent() {
    return $this->owner->raw->items[$this->id]['content'];
  }
  
  public function getip() {
    return $this->owner->raw->items[$this->id]['ip'];
  }
  
}//class

?>