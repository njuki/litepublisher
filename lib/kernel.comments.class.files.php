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
    
    //������ �� �����
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

//comments.manager.class.php
class tcommentmanager extends tevents {
  public $items;
  
  public static function instance() {
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
    $post = tpost::instance($idpost);
    if ($post->status != 'published') return;
    $item = $post->comments->items[$id];
    $item['id'] = $id;
    $item['post'] = $idpost;
    $item['title'] = $post->title;
    $item['posturl'] =     $post->lastcommenturl;
    
    $comusers = tcomusers::instance($idpost);
    $author = $comusers->items[$item['author']];
    $item['name'] = $author['name'];
    $item['email'] = $author['email'];
    $item['url'] = $author['url'];
    
    if (count($this->items) >= $this->maxrecent) array_pop($this->items);
    array_unshift($this->items, $item);
    $this->save();
  }
  
  public function add($idpost, $name, $email, $url, $content, $ip) {
    $comusers = dbversion ? tcomusers ::instance() : tcomusers ::instance($idpost);
    $idauthor = $comusers->add($name, $email, $url, $ip);
    return $this->addcomment($idpost, $idauthor, $content, $ip);
  }
  
  public function addcomment($idpost, $idauthor, $content, $ip) {
    $status = litepublisher::$classes->spamfilter->createstatus($idauthor, $content);
    $comments = tcomments::instance($idpost);
    $id = $comments->add($idauthor,  $content, $status, $ip);
    
    if (!dbversion && $status == 'approved') $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    $this->sendmail($id, $idpost);
    return $id;
  }
  
  public function edit($id, $idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::instance() : tcomusers ::instance($idpost);
    $idauthor = $comusers->add($name, $email, $url, '');
    return $this->editcomment($id, $idpost, $idauthor, $content);
  }
  
  public function editcomment($id, $idpost, $idauthor, $content) {
    $comments = tcomments::instance($idpost);
    if (!$comments->edit($id, $idauthor,  $content)) return false;
    //if (!dbversion && $status == 'approved') $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->edited($id, $idpost);
    return true;
  }
  
  public function reply($idreply, $idpost, $content) {
    $status = 'approved';
    $idpost = (int) $idpost;
    $email = litepublisher::$options->fromemail;
    $site = litepublisher::$site->url . litepublisher::$site->home;
    $name = 'Admin';
    if (class_exists('tprofile')) {
      $profile = tprofile::instance();
      $email = $profile->mbox!= '' ? $profile->mbox : $email;
      $name = $profile->nick != '' ? $profile->nick : 'Admin';
    }
    $comusers = tcomusers::instance($idpost);
    $idauthor = $comusers->add($name, $email, $site, '');
    $comments = tcomments::instance($idpost);
    $id = $comments->add($idauthor,  $content, $status, '');
    
    if (!dbversion) $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    //$this->sendmail($id, $idpost);
    return $id;
  }
  
  private function dochanged($id, $idpost) {
    if (dbversion) {
      $comments = tcomments::instance($idpost);
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
      //update trust
      try {
        $item = $comments->getitem($id);
        $idauthor = $item['author'];
        $comusers = tcomusers::instance($idpost);
        $comusers->setvalue($idauthor, 'trust', $comments->db->getcount("author = $idauthor and status = 'approved' limit 5"));
      } catch (Exception $e) {
      }
    }
    
    $post = tpost::instance($idpost);
    $post->clearcache();
    $this->changed($id, $idpost);
  }
  
  public function delete($id, $idpost) {
    $comments = tcomments::instance($idpost);
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
      $comments = tcomments::instance($idpost);
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
    $comments = tcomments::instance($idpost);
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
    $comusers = tcomusers::instance(0);
    $item = $comusers->getitem($idauthor);
    return $this->checktrust($item['trust']);
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->sendnotification) return;
    $comments = tcomments::instance($idpost);
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::instance();
    $adminurl = litepublisher::$site->url . '/admin/comments/'. litepublisher::$site->q . "id=$id&post=$idpost";
    $ref = md5(litepublisher::$secret . $adminurl);
    $adminurl .= "&ref=$ref&action";
    $args->adminurl = $adminurl;
    
    $mailtemplate = tmailtemplate::instance('comments');
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
      
      protected function create() {
        parent::create();
        $this->table ='commentskept';
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
      
      protected function create() {
        parent::create();
        $this->basename ='comments.kept';
      }
      
      public function afterload() {
        parent::AfterLoad();
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
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->cache = false;
  }
  
  public static function getcomuser($postid) {
    if (!empty($_COOKIE["userid"])) {
      $comusers = tcomusers::instance($postid);
      $user = $comusers->fromcookie($_COOKIE['userid']);
      if (!dbversion && !$user && !empty($_COOKIE["idpost"])) {
        $comusers2 = tcomusers::instance( (int) $_COOKIE['idpost']);
        $user = $comusers2->fromcookie($_COOKIE['userid']);
      }
      return $user;
    }
    return false;
  }
  
  public static function printform($postid, $themename) {
    $result = '';
    $self = self::instance();
    $lang = tlocal::instance('comment');
    $theme = ttheme::getinstance($themename);
    $args = targs::instance();
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
      $subscribers = tsubscribers::instance();
      $args->subscribe = $subscribers->subscribed($postid, $user['id']);
      
      $comments = tcomments::instance($postid);
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
    
    $kept = new tkeptcomments();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
      $confirmid  = $kept->add($values);
      return tsimplecontent::html($this->getconfirmform($confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!($values = $kept->getitem($confirmid))) {
      return tsimplecontent::content(tlocal::$data['commentform']['notfound']);
    }
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = litepublisher::$classes->posts;
    if(!$posts->itemexists($postid)) return tsimplecontent::content(tlocal::$data['default']['postnotfound']);
    $post = tpost::instance($postid);
    
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
    
    $lang = tlocal::instance('comment');
    if (!$this->checkspam($values['antispam']))   return tsimplecontent::content($lang->spamdetected);
    if (empty($values['content'])) return tsimplecontent::content($lang->emptycontent);
    if (empty($values['name'])) return tsimplecontent::content($lang->emptyname);
    if (!tcontentfilter::ValidateEmail($values['email'])) return tsimplecontent::content($lang->invalidemail);
    if (!$post->commentsenabled) return tsimplecontent::content($lang->commentsdisabled);
    if ($post->status != 'published')  return tsimplecontent::content($lang->commentondraft);
    if (litepublisher::$options->checkduplicate) {
      if (litepublisher::$classes->spamfilter->checkduplicate($postid, $values['content']) ) return tsimplecontent::content($lang->duplicate);
    }
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
    $users = tcomusers::instance($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    $usercookie = $users->getcookie($uid);
    if (!litepublisher::$classes->spamfilter->canadd( $uid)) return tsimplecontent::content($lang->toomany);
    
    $subscribers = tsubscribers::instance();
    $subscribers->update($post->id, $uid, $values['subscribe']);
    
    litepublisher::$classes->commentmanager->addcomment($post->id, $uid, $values['content'], $values['ip']);
    
    $idpostcookie = dbversion ? '' : "@setcookie('idpost', '$post->id', time() + 30000000,  '/', false);";
    return "<?php
    @setcookie('userid', '$usercookie', time() + 30000000,  '/', false);
    $idpostcookie
    @header('Location: " . litepublisher::$site->url . "$posturl');
    ?>";
  }
  
  private function getconfirmform($confirmid) {
    ttheme::$vars['lang'] = tlocal::instance($this->basename);
    $args = targs::instance();
    $args->confirmid = $confirmid;
    $theme = tsimplecontent::gettheme();
    return $theme->parsearg(
    $theme->templates['content.post.templatecomments.confirmform'], $args);
    //$theme->content->post->templatecomments->confirmform, $args);
  }
  
}//class

//comments.spamfilter.class.php
class tspamfilter extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'spamfilter';
  }
  
  public function createstatus($idauthor, $content) {
    if (!litepublisher::$options->filtercommentstatus) return litepublisher::$options->DefaultCommentStatus;
    if (litepublisher::$options->DefaultCommentStatus == 'approved') return 'approved';
    $manager = tcommentmanager::instance();
    if ($manager->trusted($idauthor)) return  'approved';
    return 'hold';
  }
  
  public function canadd($idauthor) {
    return true;
  }
  
  public function checkduplicate($idpost, $content) {
    $comments = tcomments::instance($idpost);
    $content = trim($content);
    if (dbversion) {
      $hash = md5($content);
      return $comments->raw->findid("hash = '$hash'");
    } else {
      return $comments->raw->IndexOf('content', $content);
    }
  }
  
}//class

//comments.subscribers.class.php
class tsubscribers extends titemsposts {
  
  public static function instance() {
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
        $manager->unsubscribeclass($this);
      }
    }
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->enabled) return;
    $comments = tcomments::instance($idpost);
    if (!$comments->itemexists($id)) return;
    $item = $comments->getitem($id);
    if (dbversion) {
      if (($item['status'] != 'approved')) return;
    }
    
    $cron = tcron::instance();
    $cron->add('single', get_class($this),  'cronsendmail', array((int) $id, (int) $idpost));
  }
  
  public function cronsendmail($arg) {
    $id = $arg[0];
    $pid = $arg[1];
    $comments = tcomments::instance($pid);
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    $subscribers  = $this->getitems($pid);
    if (!$subscribers  || (count($subscribers ) == 0)) return;
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $mailtemplate = tmailtemplate::instance('comments');
    $subject = $mailtemplate->subscribesubj ();
    $body = $mailtemplate->subscribebody();
    $body .= sprintf("\n%s/admin/subscribers/%suserid=", litepublisher::$site->url, litepublisher::$site->q);
    
    $comusers = tcomusers::instance();
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if ($user['email'] == $comment->email) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
      tmailer::sendmail(litepublisher::$site->name, $this->fromemail,  $user['name'], $user['email'],
      $subject, $body . $user['cookie']);
    }
  }
  
}//class

//comments.users.class.files.php
class tcomusers extends titems {
  public $pid;
  private static $instances;
  
  public static function instance($pid) {
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
        if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) {
          $parsed['scheme']= 'http';
        }
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
    $manager = tcommentmanager::instance();
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
    $manager = tcommentmanager::instance();
    $manager->authoredited($id);
    return $id;
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::instance();
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
    $this->cache = false;
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    $idpost = isset($_GET['post']) ? (int) $_GET['post'] : 1;
    if ($idpost != $this->pid) {
      $this->pid = $idpost;
      $this->load();
    }
    
    try {
      $item = $this->getitem($id);
    } catch (Exception $e) {
      return turlmap::redir301('/');
    }
    
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . litepublisher::$site->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    turlmap::redir($url);
  }
  
}//class

//template.comments.class.php
class ttemplatecomments extends tdata {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
public function load() {}
public function save() {}
  
  public function getcomments($idpost) {
    $result = '';
    $urlmap = turlmap::instance();
    $idpost = (int) $idpost;
    $post = tpost::instance($idpost);
    //    if (($post->commentscount == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->getcommentslink($post);
    $lang = tlocal::instance('comment');
    $comments = tcomments::instance($idpost);
    $list = $comments->getcontent();
    
    $theme = ttheme::instance();
    $tml = $theme->content->post->templatecomments->comments;
    $args = targs::instance();
    $args->count = $post->cmtcount;
    $result .= $tml->count($args);
    $result .= $list;
    
    if (($urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::instance($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo tcommentform::printform($idpost, '$theme->name'); ?>\n";
    } else {
      $result .= $theme->parse($theme->content->post->templatecomments->closed);
    }
    return $result;
  }
  
} //class

//widget.comments.class.php
class tcommentswidget extends twidget {
  
  public static function instance() {
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
    return tlocal::$data['default']['recentcomments'];
  }
  
  public function getcontent($id, $sidebar) {
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent($this->maxcount);
    if (count($recent) == 0) return '';
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('comments', $sidebar);
    $url = litepublisher::$site->url;
    $args = targs::instance();
    $args->onrecent = tlocal::$data['comment']['onrecent'];
    foreach ($recent as $item) {
      $args->add($item);
      $args->link = $url . $item['posturl'];
      $args->content = tcontentfilter::getexcerpt($item['content'], 120);
      $result .= $theme->parsearg($tml,$args);
    }
    return $theme->getwidgetcontent($result, 'comments', $sidebar);
  }
  
  public function changed($id, $idpost) {
    $this->expire();
  }
  
}//class

?>