<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
//comments.class.db.php
class tcomments extends titems {
  public $rawtable;
  private $pid;
  
  public static function instance($pid = 0) {
    $result = getinstance(__class__);
    if ($pid > 0) $result->pid = $pid;
    return $result;
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'comments';
    $this->rawtable = 'rawcomments';
    $this->basename = 'comments';
    $this->addevents('edited');
    $this->pid = 0;
  }
  
  public function add($idauthor, $content, $status, $ip) {
    if ($idauthor == 0) $this->error('Author id = 0');
    $filter = tcontentfilter::instance();
    $filtered = $filter->filtercomment($content);
    
    $item = array(
    'post' => $this->pid,
    'parent' => 0,
    'author' => (int) $idauthor,
    'posted' => sqldate(),
    'content' =>$filtered,
    'status' => $status
    );
    
    $id = (int) $this->db->add($item);
    $item['rawcontent'] = $content;
    
    $comusers = tcomusers::instance();
    if ($author = $comusers->getitem($idauthor)) {
      $item = $item + $author;
    } else {
      $this->error(sprintf('Author %d not found', $idauthor));
    }
    
    $item['id'] = $id;
    $this->items[$id] = $item;
    
    $this->getdb($this->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'ip' => $ip,
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    return $id;
  }
  
  public function edit($id, $idauthor, $content) {
    if (!$this->itemexists($id)) return false;
    if ($idauthor == 0) $idauthor = $this->db->getvalue($id, 'author');
    $filter = tcontentfilter::instance();
    $item = array(
    'id' => (int) $id,
    'author' => (int)$idauthor,
    'content' => $filter->filtercomment($content)
    );
    
    $this->db->updateassoc($item);
    
    $item['rawcontent'] = $content;
    $this->items[$id] = $item + $this->items[$id];
    
    $this->getdb($this->rawtable)->updateassoc(array(
    'id' => $id,
    'modified' => sqldate(),
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    return true;
  }
  
  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function delete($id) {
    return $this->db->setvalue($id, 'status', 'deleted');
  }
  
  public function setstatus($id, $status) {
    return $this->db->setvalue($id, 'status', $status);
  }
  
  public function getcount($where = '') {
    return $this->db->getcount($where);
  }
  
  public function select($where, $limit) {
    if ($where != '') $where .= ' and ';
    $comusers = tcomusers::instance();
    $authors = $comusers->thistable;
    $table = $this->thistable;
    $res = litepublisher::$db->query("select $table.*, $authors.name, $authors.email, $authors.url, $authors.trust from $table, $authors
    where $where $authors.id = $table.author $limit");
    
    return $this->res2items($res);
  }
  
  public function getraw() {
    return $this->getdb($this->rawtable);
  }
  
  public function getapprovedcount() {
    return $this->db->getcount("post = $this->pid and status = 'approved'");
  }
  
  public function insert($idauthor, $content, $ip, $posted, $status) {
    $filter = tcontentfilter::instance();
    $filtered = $filter->filtercomment($content);
    $item = array(
    'post' => $this->pid,
    'parent' => 0,
    'author' => $idauthor,
    'posted' => sqldate($posted),
    'content' =>$filtered,
    'status' => $status
    );
    
    $id =$this->db->add($item);
    $item['rawcontent'] = $content;
    $this->items[$id] = $item;
    
    $this->getdb($this->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate($posted),
    'modified' => sqldate(),
    'ip' => $ip,
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    return $id;
  }
  
  
  public function getmoderator() {
    if (!litepublisher::$options->admincookie) return false;
    if (litepublisher::$options->group == 'admin') return true;
    $groups = tusergroups::instance();
    return $groups->hasright(litepublisher::$options->group, 'moderator');
  }
  
  public function getcontent() {
    $result = $this->getcontentwhere('approved', '');
    if (!$this->moderator) return $result;
    $theme = ttheme::instance();
    tlocal::loadlang('admin');
    $args = targs::instance();
    $post = tpost::instance($this->pid);
    if ($post->commentpages == litepublisher::$urlmap->page) {
      $result .= $this->getcontentwhere('hold', '');
    } else {
      //add empty list of hold comments
      $args->comment = '';
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'], $args);
    }
    
    $args->comments = $result;
    return $theme->parsearg($theme->templates['content.post.templatecomments.moderateform'], $args);
  }
  
  public function getholdcontent($idauthor) {
    if (litepublisher::$options->admincookie) return '';
    return $this->getcontentwhere('hold', "and $this->thistable.author = $idauthor");
  }
  
  private function getcontentwhere($status, $whereauthor) {
    $result = '';
    $post = tpost::instance($this->pid);
    if ($status == 'approved') {
      $from = litepublisher::$options->commentpages  ? (litepublisher::$urlmap->page - 1) * litepublisher::$options->commentsperpage : 0;
      $count = litepublisher::$options->commentpages  ? litepublisher::$options->commentsperpage : $post->commentscount;
    } else {
      $from = 0;
      $count = litepublisher::$options->commentsperpage;
    }
    
    $table = $this->thistable;
    $items = $this->select("$table.post = $this->pid $whereauthor  and $table.status = '$status'",
    "order by $table.posted asc limit $from, $count");
    
    $args = targs::instance();
    $args->from = $from;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $lang = tlocal::instance('comment');
    $theme = ttheme::instance();
    if ($ismoder = $this->moderator) {
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
    
    $index = $from;
    $class1 = $tmlcomment->class1;
    $class2 = $tmlcomment->class2;
    foreach ($items as $id) {
      $comment->id = $id;
      $args->index = ++$index;
      $args->class = ($index % 2) == 0 ? $class1 : $class2;
      $result .= $theme->parsearg($tml, $args);
    }
    unset(ttheme::$vars['comment']);
    if (!$ismoder) {
      if ($result == '') return '';
    }
    
    if ($status == 'hold') {
      $tml = $theme->templates['content.post.templatecomments.holdcomments'];
    } else {
      $tml = $theme->templates['content.post.templatecomments.comments'];
    }
    
    $args->from = $from + 1;
    $args->comment = $result;
    return $theme->parsearg($tml, $args);
  }
  
}//class

class tcomment extends tdata {
  
  public function __construct($id) {
    if (!isset($id)) return false;
    parent::__construct();
    $this->table = 'comments';
    $id = (int) $id;
    if ($id > 0) $this->setid($id);
  }
  
  public function setid($id) {
    $comments = tcomments::instance();
    $this->data = $comments->getitem($id);
  }
  
  public function save() {
    extract($this->data, EXTR_SKIP);
    $this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));
    
    $this->getdb($this->rawtable)->UpdateAssoc(array(
    'id' => $id,
    'modified' => sqldate(),
    'rawcontent' => $rawcontent,
    'hash' => basemd5($rawcontent)
    ));
  }
  
  public function getauthorlink() {
    $name = $this->data['name'];
    $url = $this->data['url'];
    if ($url == '')  return $name;
    $manager = tcommentmanager::instance();
    if ($manager->hidelink || !$manager->checktrust($this->trust)) return $name;
    $rel = $manager->nofollow ? 'rel="nofollow noindex"' : '';
    if ($manager->redir) {
      return sprintf('<a %s href="%s/comusers.htm%sid=%d">%s</a>',$rel,
      litepublisher::$site->url, litepublisher::$site->q, $this->author, $name);
    } else {
      return sprintf('<a class="url fn" %s href="%s">%s</a>',
      $rel,$url, $name);
    }
  }
  
  public function getdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
  }
  
  public function Getlocalstatus() {
    return tlocal::$data['commentstatus'][$this->status];
  }
  
  public function getposted() {
    return strtotime($this->data['posted']);
  }
  
  public function setposted($date) {
    $this->data['posted'] = sqldate($date);
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function getwebsite() {
    return $this->data['url'];
  }
  
  public function geturl() {
    $post = tpost::instance($this->post);
    return $post->link . "#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::instance($this->post);
    return $post->title;
  }
  
  public function getrawcontent() {
    if (isset($this->data['rawcontent'])) return $this->data['rawcontent'];
    $comments = tcomments::instance($this->post);
    return $comments->raw->getvalue($this->id, 'rawcontent');
  }
  
  public function setrawcontent($s) {
    $this->data['rawcontent'] = $s;
    $filter = tcontentfilter::instance();
    $this->data['content'] = $filter->filtercomment($s);
  }
  
  public function getip() {
    if (isset($this->data['ip'])) return $this->data['ip'];
    $comments = tcomments::instance($this->post);
    return $comments->raw->getvalue($this->id, 'ip');
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
    $status = litepublisher::$classes->spamfilter->createstatus($idpost, $idauthor, $content, $ip);
    if (!$status) return false;
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
    $name = litepublisher::$site->author;
    /*
    if (class_exists('tprofile')) {
      $profile = tprofile::instance();
      $email = $profile->mbox!= '' ? $profile->mbox : $email;
      $name = $profile->nick != '' ? $profile->nick : 'Admin';
    }
    */
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
      
      public static function instance() {
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
      
      public static function instance() {
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
  
  public static function instance() {
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
      $comusers = tcomusers::instance($postid);
      $user = $comusers->fromcookie($cookie);
      $comusers->loadall();
      if (!dbversion && !$user && !empty($_COOKIE["idpost"])) {
        $comusers2 = tcomusers::instance( (int) $_COOKIE['idpost']);
        $user = $comusers2->fromcookie($cookie);
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
    
    $kept = tkeptcomments::instance();
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
    if (!($values = $kept->getitem($confirmid))) {
      //return tsimplecontent::content(tlocal::$data['commentform']['notfound']);
      return $this->htmlhelper->geterrorcontent(tlocal::$data['commentform']['notfound']);
    }
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = litepublisher::$classes->posts;
    if(!$posts->itemexists($postid)) {
      //return tsimplecontent::content(tlocal::$data['default']['postnotfound']);
      return $this->htmlhelper->geterrorcontent(tlocal::$data['default']['postnotfound']);
    }
    
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
    $users = tcomusers::instance($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    if (!litepublisher::$classes->spamfilter->canadd( $uid)) {
      return $this->htmlhelper->geterrorcontent($lang->toomany);
    }
    
    $subscribers = tsubscribers::instance();
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
    ttheme::$vars['lang'] = tlocal::instance($this->basename);
    $args = targs::instance();
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
  
  public static function instance() {
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
    $manager = tcommentmanager::instance();
    if ($manager->trusted($idauthor)) return  'approved';
    return 'hold';
  }
  
  public function canadd($idauthor) {
    if ($this->is_spamer($idauthor)) return false;
    return true;
  }
  
  public function checkduplicate($idpost, $content) {
    $comments = tcomments::instance($idpost);
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

//comments.users.class.db.php
class tcomusers extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'comusers';
    $this->basename = 'comusers';
  }
  
  public function add($name, $email, $url, $ip) {
    if ($id = $this->find($name, $email, $url)) {
      $this->db->setvalue($id, 'ip', $ip);
      return $id;
    }
    
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
    $id = $this->db->add(array(
    'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'ip' => $ip,
    'cookie' => md5uniq(),
    ));
    
    litepublisher::$classes->commentmanager->authoradded($id);
    return $id;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->db->UpdateAssoc(array(
    'id' => $id,
    'name' => $name,
    'email' => $email,
    'url' => $url,
    'ip' => $ip
    ));
    $manager = tcommentmanager::instance();
    $manager->authoredited($id);
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::instance();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    return $this->db->finditem('cookie = '. dbquote($cookie));
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    $id = $this->db->findid('name = '. dbquote($name) . ' and email = '. dbquote($email) .' and url = '. dbquote($url));
    return $id == '0' ? false : (int) $id;
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if (!$this->itemexists($id)) return "<?php turlmap::redir301('/');";;
    $item = $this->getitem($id);
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . litepublisher::$site->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    return "<?php turlmap::redir('$url');";
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
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $post->getcommentslink();
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
      if (litepublisher::$options->autocmtform) {
        $result .=  "<?php  echo tcommentform::printform($idpost, '$theme->name'); ?>\n";
      } else {
        $lang = tlocal::instance('comment');
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
  
  public function changed() {
    $this->expire();
  }
  
}//class

?>