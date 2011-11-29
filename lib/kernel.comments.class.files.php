<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
//comments.class.files.php
class tcomments extends titems {
  public $pid;
  private $rawitems;
  private $holditems;
  private static $instances;
  
  public static function i($pid = 0) {
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
    $filter = tcontentfilter::i();
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
    
    $filter = tcontentfilter::i();
    
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
    
    //������ �� �����
    $comusers = tcomusers::i($this->pid);
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
    $filter = tcontentfilter::i();
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
    $groups = tusergroups::i();
    return $groups->hasrigt(litepublisher::$options->group, 'moderator');
  }
  
  public function getcontent() {
    $result = $this->dogetcontent(false, 0);
    if (!$this->moderator) return $result;
    $theme = ttheme::i();
    $lang = tlocal::admin('comment');
    $post = tpost::i($this->pid);
    if ($post->commentpages == litepublisher::$urlmap->page) {
      $result .= $this->hold->dogetcontent(true, 0);
    } else {
      //add empty list of hold comments
      $args = targs::i();
      $args->comment = '';
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'], $args);
    }
    $args = targs::i();
    $args->comments = $result;
    return $theme->parsearg($theme->templates['content.post.templatecomments.moderateform'], $args);
  }
  
  public function dogetcontent($hold, $idauthor) {
    $result = '';
    $post = tpost::i($this->pid);
    $from = 0;
    $items = array_keys($this->items);
    if (!$hold) {
      if (litepublisher::$options->commentpages ) {
        $page = min(litepublisher::$urlmap->page, $post->commentpages );
        if (litepublisher::$options->comments_invert_order) $page = max(0, $post->commentpages  - $page) + 1;
        $from = ($page - 1) * litepublisher::$options->commentsperpage;
        $items = array_slice($items, $from, litepublisher::$options->commentsperpage, true);
      }
    }
    
    $ismoder = $this->moderator;
    $theme = ttheme::i();
    $args = targs::i();
    if (count($items) > 0) {
      $args->from = $from;
      $comment = new tcomment($this);
      ttheme::$vars['comment'] = $comment;
      if ($hold) $comment->status = 'hold';
      $lang = tlocal::i('comment');
      
      if ($ismoder) {
        tlocal::usefile('admin');
        $moderate =$theme->templates['content.post.templatecomments.comments.comment.moderate'];
      } else {
        $moderate = '';
      }
      
      $tmlcomment= $theme->gettag('content.post.templatecomments.comments.comment');;
      $tml = strtr((string) $tmlcomment, array(
      '$moderate' => $moderate,
      '$quotebuttons' => $post->commentsenabled ? $tmlcomment->quotebuttons : ''
      ));
      
      $index = $from;
      $class1 = $tmlcomment->class1;
      $class2 = $tmlcomment->class2;
      foreach ($items as $id) {
        //one method for approved and hold comments
        if (!litepublisher::$options->admincookie && $hold) {
          if ($idauthor != $this->items[$id]['author']) continue;
        }
        $comment->id = $id;
        $args->index = ++$index;
        $args->class = ($index % 2) == 0 ? $class1 : $class2;
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
  
  public static function i($pid = 0) {
    $owner = tcomments::i($pid);
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
    $filter = tcontentfilter::i();
    $this->owner->items[$this->id]['content'] = $filter->filtercomment($value);
    $this->save();
    $this->owner->raw->items[$this->id]['content'] =  $value;
    $this->owner->raw->save();
  }
  
  private function getauthoritem() {
    $comusers = tcomusers::i($this->owner->pid);
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
    $comusers = tcomusers::i($idpost);
    $item = $comusers->getitem($this->author);
    $name = $item['name'];
    
    if (empty($url)) return $name;
    $manager = tcommentmanager::i();
    if ($manager->hidelink) return $name;
    $rel = $manager->nofollow ? 'rel="nofollow"' : '';
    if ($manager->redir) {
      return "<a $rel href=\"" . litepublisher::$site->url . "/comusers.htm" . litepublisher::$site->q . "id=$this->author&post=$idpost\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
  }
  
  public function getdate() {
    $theme = ttheme::i();
    return tlocal::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
  }
  
  public function getlocalstatus() {
    return tlocal::get('commentstatus', $this->status);
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function geturl() {
    $post = tpost::i($this->owner->pid);
    return "$post->link#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::i($this->owner->pid);
    return $post->title;
  }
  
  public function getrawcontent() {
    return $this->owner->raw->items[$this->id]['content'];
  }
  
  public function getip() {
    return $this->owner->raw->items[$this->id]['ip'];
  }
  
}//class

//comments.manager.class.php
class tcommentmanager extends tevents {
  public $items;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->addevents('added', 'deleted', 'edited', 'changed', 'approved',
    'authoradded', 'authordeleted', 'authoredited');
    if (!dbversion) $this->addmap('items', array());
    $this->data['sendnotification'] =  true;
    $this->data['trustlevel'] = 2;
    $this->data['hidelink'] = false;
    $this->data['redir'] = true;
    $this->data['nofollow'] = false;
    $this->data['maxrecent'] =  20;
  }
  
  public function getcount() {
    if (!dbversion)  return 0;
    litepublisher::$db->table = 'comments';
    return litepublisher::$db->getcount();
  }
  
  private function indexofrecent($id, $idpost) {
    foreach ($this->items as $i => $item) {
      if ($id == $item['id'] && $idpost == $item['post']) return $i;
    }
    return false;
  }
  
  private function deleterecent($id, $idpost) {
    if (is_int($i = $this->indexofrecent($id, $idpost))) {
      array_splice($this->items, $i, 1);
      $this->save();
    }
  }
  
  private function addrecent($id, $idpost) {
    if (is_int($i = $this->indexofrecent($id, $idpost)))  return;
    $post = tpost::i($idpost);
    if ($post->status != 'published') return;
    $item = $post->comments->items[$id];
    $item['id'] = $id;
    $item['post'] = $idpost;
    $item['title'] = $post->title;
    $item['posturl'] =     $post->lastcommenturl;
    
    $comusers = tcomusers::i($idpost);
    $author = $comusers->items[$item['author']];
    $item['name'] = $author['name'];
    $item['email'] = $author['email'];
    $item['url'] = $author['url'];
    
    if (count($this->items) >= $this->maxrecent) array_pop($this->items);
    array_unshift($this->items, $item);
    $this->save();
  }
  
  public function editrecent($id, $idpost) {
    if (!is_int($i = $this->indexofrecent($id, $idpost)))  return;
    $item = tcomments::i($idpost)->items[$id];
    $this->items[$i]['content'] = $item['content'];
    $this->save();
  }
  
  
  public function add($idpost, $name, $email, $url, $content, $ip) {
    $comusers = dbversion ? tcomusers ::i() : tcomusers ::i($idpost);
    $idauthor = $comusers->add($name, $email, $url, $ip);
    return $this->addcomment($idpost, $idauthor, $content, $ip);
  }
  
  public function addcomment($idpost, $idauthor, $content, $ip) {
    $status = litepublisher::$classes->spamfilter->createstatus($idpost, $idauthor, $content, $ip);
    if (!$status) return false;
    $comments = tcomments::i($idpost);
    $id = $comments->add($idauthor,  $content, $status, $ip);
    
    if (!dbversion && $status == 'approved') $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    $this->sendmail($id, $idpost);
    return $id;
  }
  
  public function edit($id, $idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::i() : tcomusers ::i($idpost);
    $idauthor = $comusers->add($name, $email, $url, '');
    return $this->editcomment($id, $idpost, $idauthor, $content);
  }
  
  public function editcomment($id, $idpost, $idauthor, $content) {
    $comments = tcomments::i($idpost);
    if (!$comments->edit($id, $idauthor,  $content)) return false;
    if (!dbversion && $status == 'approved') $this->editrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->edited($id, $idpost);
    return true;
  }
  
  public function reply($idreply, $idpost, $content) {
    $status = 'approved';
    $idpost = (int) $idpost;
    $email = litepublisher::$options->fromemail;
    $site = litepublisher::$site->url . litepublisher::$site->home;
    $name = litepublisher::$site->author;
    /*
    if (class_exists('tprofile')) {
      $profile = tprofile::i();
      $email = $profile->mbox!= '' ? $profile->mbox : $email;
      $name = $profile->nick != '' ? $profile->nick : 'Admin';
    }
    */
    $comusers = tcomusers::i($idpost);
    $idauthor = $comusers->add($name, $email, $site, '');
    $comments = tcomments::i($idpost);
    $id = $comments->add($idauthor,  $content, $status, '');
    
    if (!dbversion) $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    //$this->sendmail($id, $idpost);
    return $id;
  }
  
  private function dochanged($id, $idpost) {
    if (dbversion) {
      $comments = tcomments::i($idpost);
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
      //update trust
      try {
        $item = $comments->getitem($id);
        $idauthor = $item['author'];
        $comusers = tcomusers::i($idpost);
        $comusers->setvalue($idauthor, 'trust', $comments->db->getcount("author = $idauthor and status = 'approved' limit 5"));
      } catch (Exception $e) {
      }
    }
    
    $post = tpost::i($idpost);
    $post->clearcache();
    $this->changed($id, $idpost);
  }
  
  public function delete($id, $idpost) {
    $comments = tcomments::i($idpost);
    if ($comments->delete($id)) {
      if (!dbversion) $this->deleterecent($id, $idpost);
      $this->deleted($id, $idpost);
      $this->dochanged($id, $idpost);
      return true;
    }
    return false;
  }
  
  public function postdeleted($idpost) {
    if (dbversion) {
      $comments = tcomments::i($idpost);
      $comments->db->update("status = 'deleted'", "post = $idpost");
    } else {
      $deleted = false;
      foreach ($this->items as $i => $item) {
        if ($idpost == $item['post']) {
          unset($this->items[$i]);
          //array_splice($this->items, $i, 1);
          $deleted = true;
        }
      }
      if ($deleted) {
        $this->save();
        $this->changed();
      }
    }
  }
  
  public function setstatus($id, $idpost, $status) {
    if (!in_array($status, array('approved', 'hold', 'spam')))  return false;
    $comments = tcomments::i($idpost);
    if ($comments->setstatus($id, $status)) {
      if (!dbversion){
        if ($status == 'approved') {
          $this->addrecent($id, $idpost);
        } else {
          $this->deleterecent($id, $idpost);
        }
      }
      $this->dochanged($id, $idpost);
      return true;
    }
    return false;
  }
  
  public function checktrust($value) {
    return $value >= $this->trustlevel;
  }
  
  public function trusted($idauthor) {
    if (!dbversion) return true;
    $comusers = tcomusers::i(0);
    $item = $comusers->getitem($idauthor);
    return $this->checktrust($item['trust']);
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->sendnotification) return;
    $comments = tcomments::i($idpost);
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $adminurl = litepublisher::$site->url . '/admin/comments/'. litepublisher::$site->q . "id=$id&post=$idpost";
    $ref = md5(litepublisher::$secret . $adminurl);
    $adminurl .= "&ref=$ref&action";
    $args->adminurl = $adminurl;
    
    $mailtemplate = tmailtemplate::i('comments');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body, true);
  }
  
  //status supports only db version
  public function getrecent($count, $status = 'approved') {
    if (dbversion) {
      $db = litepublisher::$db;
      $result = $db->res2assoc($db->query("select $db->comments.*,
      $db->comusers.name as name, $db->comusers.email as email, $db->comusers.url as url,
      $db->posts.title as title, $db->posts.commentscount as commentscount,
      $db->urlmap.url as posturl
      from $db->comments, $db->comusers, $db->posts, $db->urlmap
      where $db->comments.status = '$status' and
      $db->comusers.id = $db->comments.author and
      $db->posts.id = $db->comments.post and
      $db->urlmap.id = $db->posts.idurl and
      $db->posts.status = 'published'
      order by $db->comments.posted desc limit $count"));
      
      if (litepublisher::$options->commentpages) {
        foreach ($result as $i => $item) {
          $page = ceil($item['commentscount'] / litepublisher::$options->commentsperpage);
          if ($page > 1) $result[$i]['posturl']= rtrim($item['posturl'], '/') . "/page/$page/";
        }
      }
      return $result;
    } else {
      if ($count >= count($this->items)) return $this->items;
      return array_slice($this->items, 0, $count);
    }
  }
  
}//class

//comments.form.class.php
if (!class_exists('tkeptcomments', false)) {
  if (dbversion) {
    class tkeptcomments extends tdata {
      
      public static function i() {
        return getinstance(__class__);
      }
      
      protected function create() {
        parent::create();
        $this->table ='commentskept';
        
      }
      
      public function deleteold() {
        $this->db->delete(sprintf("posted < '%s' - INTERVAL 20 minute ", sqldate()));
      }
      
      public function add($values) {
        $confirmid = md5uniq();
        $this->db->add(array(
        'id' => $confirmid,
        'posted' => sqldate(),
        'vals' => serialize($values)
        ));
        return $confirmid;
      }
      
      public function getitem($confirmid) {
        if ($item = $this->db->getitem(dbquote($confirmid))) {
          return unserialize($item['vals']);
        }
        return false;
      }
      
    }//class
    
  } else {
    
    class tkeptcomments extends titems {
      
      public static function i() {
        return getinstance(__class__);
      }
      
      protected function create() {
        parent::create();
        $this->basename ='comments.kept';
      }
      
      public function deleteold() {
        foreach ($this->items as $id => $item) {
          if ($item['date']+ 600 < time()) unset($this->items[$id]);
        }
      }
      
      public function add($values) {
        $confirmid = md5uniq();
        $this->items[$confirmid] =$values;
        $this->save();
        return $confirmid;
      }
      
      public function getitem($confirmid) {
        if (!isset($this->items[$confirmid])) return false;
        $this->save();
        return $this->items[$confirmid];
      }
      
    }//class
    
  }
}

class tcommentform extends tevents {
  public $htmlhelper;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->cache = false;
    $this->htmlhelper = $this;
  }
  
  public static function getcomuser($postid) {
    if (!empty($_COOKIE['userid'])) {
      $cookie = basemd5($_COOKIE['userid']  . litepublisher::$secret);
      $comusers = tcomusers::i($postid);
      $user = $comusers->fromcookie($cookie);
      $comusers->loadall();
      if (!dbversion && !$user && !empty($_COOKIE["idpost"])) {
        $comusers2 = tcomusers::i( (int) $_COOKIE['idpost']);
        $user = $comusers2->fromcookie($cookie);
      }
      return $user;
    }
    return false;
  }
  
  public static function printform($postid, $themename) {
    $result = '';
    $self = self::i();
    $lang = tlocal::i('comment');
    $theme = ttheme::getinstance($themename);
    $args = targs::i();
    $args->name = '';
    $args->email = '';
    $args->url = '';
    $args->subscribe = litepublisher::$options->defaultsubscribe;
    $args->content = '';
    $args->postid = $postid;
    $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
    
    if ($user = self::getcomuser($postid)) {
      $args->name = $user['name'];
      $args->email = $user['email'];
      $args->url = $user['url'];
      $subscribers = tsubscribers::i();
      $args->subscribe = $subscribers->subscribed($postid, $user['id']);
      
      $comments = tcomments::i($postid);
      if ($hold = $comments->getholdcontent($user['id'])) {
        $result .= $hold;
      }
    }
    
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
    return $result;
  }
  
  private function checkspam($s) {
    if  (!($s = @base64_decode($s))) return false;
    $sign = 'superspamer';
    if (!strbegin($s, $sign)) return false;
    $TimeKey = (int) substr($s, strlen($sign));
    return time() < $TimeKey;
  }
  
  public function request($arg) {
    if (litepublisher::$options->commentsdisabled) return 404;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Content-Type: text/plain');
      ?>";
    }
    
    $posturl = litepublisher::$site->url . '/';
    
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    
    $kept = tkeptcomments::i();
    $kept->deleteold();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
      $confirmid  = $kept->add($values);
      //return tsimplecontent::html($this->getconfirmform($confirmid));
      return $this->htmlhelper->confirm($confirmid);
    }
    
    $confirmid = $_POST['confirmid'];
    $lang = tlocal::i('commentform');
    if (!($values = $kept->getitem($confirmid))) {
      return $this->htmlhelper->geterrorcontent($lang->notfound);
    }
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = litepublisher::$classes->posts;
    if(!$posts->itemexists($postid)) {
      return $this->htmlhelper->geterrorcontent($lang->postnotfound);
    }
    
    $post = tpost::i($postid);
    
    $values = array(
    'name' => isset($values['name']) ? tcontentfilter::escape($values['name']) : '',
    'email' => isset($values['email']) ? trim($values['email']) : '',
    'url' => isset($values['url']) ? tcontentfilter::escape($values['url']) : '',
    'subscribe' => isset($values['subscribe']),
    'content' => isset($values['content']) ? trim($values['content']) : '',
    'ip' => isset($values['ip']) ? $values['ip'] : '',
    'postid' => $postid,
    'antispam' => isset($values['antispam']) ? $values['antispam'] : ''
    );
    
    $lang = tlocal::i('comment');
    if (!$this->checkspam($values['antispam']))          {
      return $this->htmlhelper->geterrorcontent($lang->spamdetected);
    }
    
    if (empty($values['content'])) return $this->htmlhelper->geterrorcontent($lang->emptycontent);
    if (empty($values['name']))       return $this->htmlhelper->geterrorcontent($lang->emptyname);
    if (!tcontentfilter::ValidateEmail($values['email'])) {
      return $this->htmlhelper->geterrorcontent($lang->invalidemail);
    }
    
    if (!$post->commentsenabled)       {
      return $this->htmlhelper->geterrorcontent($lang->commentsdisabled);
    }
    
    if ($post->status != 'published')  {
      return $this->htmlhelper->geterrorcontent($lang->commentondraft);
    }
    
    if (litepublisher::$options->checkduplicate) {
      if (litepublisher::$classes->spamfilter->checkduplicate($postid, $values['content']) ) {
        return $this->htmlhelper->geterrorcontent($lang->duplicate);
      }
    }
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
    $users = tcomusers::i($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    if (!litepublisher::$classes->spamfilter->canadd( $uid)) {
      return $this->htmlhelper->geterrorcontent($lang->toomany);
    }
    
    $subscribers = tsubscribers::i();
    $subscribers->update($post->id, $uid, $values['subscribe']);
    
    litepublisher::$classes->commentmanager->addcomment($post->id, $uid, $values['content'], $values['ip']);
    
    $cookies = array();
    $cookie = empty($_COOKIE['userid']) ? '' : $_COOKIE['userid'];
    $usercookie = $users->getcookie($uid);
    if ($usercookie != basemd5($cookie . litepublisher::$secret)) {
      $cookie= md5uniq();
      $usercookie = basemd5($cookie . litepublisher::$secret);
      $users->setvalue($uid, 'cookie', $usercookie);
    }
    $cookies['userid'] = $cookie;
    
    foreach (array('name', 'email', 'url') as $field) {
      $cookies["comuser_$field"] = $values[$field];
    }
    
    if (!dbversion) $cookies['idpost'] = $post->id;
    return $this->htmlhelper->sendcookies($cookies, litepublisher::$site->url . $posturl);
  }
  
  private function getconfirmform($confirmid) {
    ttheme::$vars['lang'] = tlocal::i($this->basename);
    $args = targs::i();
    $args->confirmid = $confirmid;
    $theme = tsimplecontent::gettheme();
    return $theme->parsearg(
    $theme->templates['content.post.templatecomments.confirmform'], $args);
  }
  
  //htmlhelper
  public function confirm($confirmid) {
    return tsimplecontent::html($this->getconfirmform($confirmid));
  }
  
  public function geterrorcontent($s) {
    return tsimplecontent::content($s);
  }
  
  public function sendcookies($cookies, $url) {
    $result = '<?php ';
    foreach ($cookies as $name => $value) {
      $result .= " setcookie('$name', '$value', time() + 30000000,  '/', false);";
    }
    
    $result .= sprintf(" header('Location: %s'); ?>", $url);
    return $result;
  }
  
}//class

//comments.spamfilter.class.php
class tspamfilter extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'spamfilter';
    $this->addevents('is_spamer', 'onstatus');
  }
  
  public function createstatus($idpost, $idauthor, $content, $ip) {
    $status = $this->onstatus($idpost, $idauthor, $content, $ip);
    if (false ===  $status) return false;
    if ($status == 'spam') return false;
    if (($status == 'hold') || ($status == 'approved')) return $status;
    if (!litepublisher::$options->filtercommentstatus) return litepublisher::$options->DefaultCommentStatus;
    if (litepublisher::$options->DefaultCommentStatus == 'approved') return 'approved';
    $manager = tcommentmanager::i();
    if ($manager->trusted($idauthor)) return  'approved';
    return 'hold';
  }
  
  public function canadd($idauthor) {
    if ($this->is_spamer($idauthor)) return false;
    return true;
  }
  
  public function checkduplicate($idpost, $content) {
    $comments = tcomments::i($idpost);
    $content = trim($content);
    if (dbversion) {
      $hash = basemd5($content);
      return $comments->raw->findid("hash = '$hash'");
    } else {
      return $comments->raw->IndexOf('content', $content);
    }
  }
  
}//class

//comments.subscribers.class.php
class tsubscribers extends titemsposts {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->data['locklist'] = '';
  }
  
  public function load() {
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    tfilestorage::save($this);
  }
  
  public function update($pid, $uid, $subscribed) {
    if ($subscribed == $this->subscribed($pid, $uid)) return;
    if (dbversion) {
      $this->remove($pid, $uid);
      if ($subscribed) $this->add($pid, $uid);
    } elseif ($subscribed) {
      $this->items[$pid][] =$uid;
      $this->save();
    } else {
      $this->remove($pid, $uid);
    }
  }
  
  public function subscribed($pid, $uid) {
    if (dbversion) {
      return $this->db->exists("post = $pid and item = $uid");
    } else {
      return isset($this->items[$pid]) && in_array($uid, $this->items[$pid]);
    }
  }
  
  public function setenabled($value) {
    if ($this->enabled != $value) {
      $this->data['enabled'] = $value;
      $this->save();
      $manager = litepublisher::$classes->commentmanager;
      if ($value) {
        $manager->lock();
        $manager->added = $this->sendmail;
        $manager->approved = $this->sendmail;
        $manager->unlock();
      } else {
        $manager->unbind($this);
      }
    }
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->enabled) return;
    $comments = tcomments::i($idpost);
    if (!$comments->itemexists($id)) return;
    $item = $comments->getitem($id);
    if (dbversion) {
      if (($item['status'] != 'approved')) return;
    }
    
    $cron = tcron::i();
    $cron->add('single', get_class($this),  'cronsendmail', array((int) $id, (int) $idpost));
  }
  
  public function cronsendmail($arg) {
    $id = $arg[0];
    $pid = $arg[1];
    $comments = tcomments::i($pid);
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    $subscribers  = $this->getitems($pid);
    if (!$subscribers  || (count($subscribers ) == 0)) return;
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $mailtemplate = tmailtemplate::i('comments');
    $subject = $mailtemplate->subscribesubj ();
    $body = $mailtemplate->subscribebody();
    $body .= sprintf("\n%s/admin/subscribers/%suserid=", litepublisher::$site->url, litepublisher::$site->q);
    
    $comusers = tcomusers::i();
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if ($user['email'] == $comment->email) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
      tmailer::sendmail(litepublisher::$site->name, $this->fromemail,  $user['name'], $user['email'],
      $subject, $body . rawurlencode($user['cookie']));
    }
  }
  
}//class

//comments.users.class.files.php
class tcomusers extends titems {
  public $pid;
  private static $instances;
  
  public static function i($pid = 0) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
    $self = litepublisher::$classes->newinstance(__class__);
    self::$instances[$pid]  = $self;
    $self->pid = $pid;
    $self->load();
    return $self;
  }
  
  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments.authors';
  }
  
  public function add($name, $email, $url, $ip) {
    if ($id = $this->find($name, $email, $url)) return $id;
    
    if (($parsed = @parse_url($url)) &&  is_array($parsed) ) {
      if ( empty($parsed['host'])) {
        $url = '';
      } else {
        if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) $parsed['scheme']= 'http';
        if (!isset($parsed['path'])) $parsed['path'] = '';
        $url = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
        if (!empty($parsed['query'])) $url .= '?' . $parsed['query'];
      }
    } else {
      $url = '';
    }
    
    $this->lock();
    $this->items[++$this->autoid] = array(
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5uniq(),
    'ip' => $ip
    );
    
    $this->unlock();
    $manager = tcommentmanager::i();
    $manager->authoradded($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->lock();
    $item = &$this->items[$id];
    $item['name'] = $name;
    $item['url'] = $url;
    $item['email'] = $email;
    $item['ip'] = $ip;
    $this->unlock();
    $manager = tcommentmanager::i();
    $manager->authoredited($id);
    return $id;
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::i();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    foreach ($this->items as $id => $item) {
      if ($cookie == $item['cookie']) {
        $item['id'] = $id;
        return $item;
      }
    }
    return false;
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    foreach ($this->items as $id => $item) {
      if (($name == $item['name'])  && ($email == $item['email']) && ($url == $item['url'])) {
        return $id;
      }
    }
    return false;
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    $idpost = isset($_GET['post']) ? (int) $_GET['post'] : 1;
    if ($idpost != $this->pid) {
      $this->pid = $idpost;
      $this->load();
    }
    
    try {
      $item = $this->getitem($id);
    } catch (Exception $e) {
      return "<?[php turlmap::redir301('/');";
    }
    
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . litepublisher::$site->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    return "<?php turlmap::redir($url);";
  }
  
}//class

//template.comments.class.php
class ttemplatecomments extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
public function load() {}
public function save() {}
  
  public function getcomments($idpost) {
    $result = '';
    $urlmap = turlmap::i();
    $idpost = (int) $idpost;
    $post = tpost::i($idpost);
    
    //if ($post->haspages && ($post->commentpages < $urlmap->page)) return $post->getcommentslink();
    
    $lang = tlocal::i('comment');
    $comments = tcomments::i($idpost);
    $list = $comments->getcontent();
    
    $theme = $post->theme;
    $args = targs::i();
    $args->count = $post->cmtcount;
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.comments.count'], $args);
    $result .= $list;
    
    if (($urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::i($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && $post->commentsenabled) {
      if (litepublisher::$options->autocmtform) {
        $result .=  "<?php  echo tcommentform::printform($idpost, '$theme->name'); ?>\n";
      } else {
        $lang = tlocal::i('comment');
        $args->name = '';
        $args->email = '';
        $args->url = '';
        $args->subscribe = litepublisher::$options->defaultsubscribe;
        $args->content = '';
        $args->postid = $idpost;
        $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
        
        $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
      }
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }
  
} //class

//widget.comments.class.php
class tcommentswidget extends twidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.comments';
    $this->cache = 'include';
    $this->template = 'comments';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] =  7;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'recentcomments');
  }
  
  public function getcontent($id, $sidebar) {
    $manager = tcommentmanager::i();
    $recent = $manager->getrecent($this->maxcount);
    if (count($recent) == 0) return '';
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('comments', $sidebar);
    $url = litepublisher::$site->url;
    $args = targs::i();
    $args->onrecent = tlocal::get('comment', 'onrecent');
    foreach ($recent as $item) {
      $args->add($item);
      $args->link = $url . $item['posturl'];
      $args->content = tcontentfilter::getexcerpt($item['content'], 120);
      $result .= $theme->parsearg($tml,$args);
    }
    return $theme->getwidgetcontent($result, 'comments', $sidebar);
  }
  
  public function changed() {
    $this->expire();
  }
  
}//class

?>