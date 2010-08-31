<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
    'hash' => md5($content)
    ));
    
    return $id;
  }
  
  public function edit($id, $idauthor, $content) {
    if (!$this->itemexists($id)) return false;
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
    'hash' => md5($content)
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
    'hash' => md5($content)
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
    $tml = $theme->content->post->templatecomments->comments;
    tlocal::loadlang('admin');
    $result .= $theme->parse($tml->hold);
    $post = tpost::instance($this->pid);
    if ($post->commentpages == litepublisher::$urlmap->page) {
      $result .= $this->getcontentwhere('hold', '');
    } else {
      //add empty list of hold comments
      $commentsid = $theme->content->post->templatecomments->comments->commentsid;
      $s = str_replace("id=\"$commentsid\"", "id=\"hold$commentsid\"", $tml->array[0]);
      $s = str_replace('<a name="comments"', '<a name="holdcomments"', $s);
      $args = targs::instance();
      $args->from = 1;
      $args->items = '';
      $result .= $theme->parsearg($s, $args);
    }
    
    $args = targs::instance();
    $args->comments = $result;
    return $theme->parsearg($theme->content->post->templatecomments->moderateform, $args);
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
    $items = $this->select("$table.post = $this->pid and $table.status = '$status' $whereauthor",
    "order by $table.posted asc limit $from, $count");
    
    $args = targs::instance();
    $args->from = $from;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $lang = tlocal::instance('comment');
    $theme = ttheme::instance();
    if ($ismoder = $this->moderator) {
      tlocal::loadlang('admin');
      $moderate =$theme->content->post->templatecomments->comments->comment->moderate;
    } else {
      $moderate = '';
    }
    $tmlcmt= $theme->content->post->templatecomments->comments->comment;
    $tml = str_replace('$moderate', $moderate, $tmlcmt->array[0]);
    
    $i = 1;
    $class1 = $tmlcmt->class1;
    $class2 = $tmlcmt->class2;
    foreach ($items as $id) {
      $comment->id = $id;
      $args->class = (++$i % 2) == 0 ? $class1 : $class2;
      $result .= $theme->parsearg($tml, $args);
    }
    
    $tml = (string) $theme->content->post->templatecomments->comments;
    if ($status == 'hold') {
      $tml = str_replace('<a name="comments"', '<a name="holdcomments"', $tml);
      $commentsid = $theme->content->post->templatecomments->comments->commentsid;
      $tml = str_replace("id=\"$commentsid\"", "id=\"hold$commentsid\"", $tml);
    }
    
    if (!$ismoder) {
      if ($result == '') return '';
    }
    
    $args = targs::instance();
    $args->items = $result;
    $args->from = $from + 1;
    return $theme->parsearg($tml, $args);
  }
  
  public function getcontentlist($tml, $class1, $class2) {
    $result = '';
    $post = tpost::instance($this->pid);
    $from = litepublisher::$options->commentpages  ? (litepublisher::$urlmap->page - 1) * litepublisher::$options->commentsperpage : 0;
    $count = litepublisher::$options->commentpages  ? litepublisher::$options->commentsperpage : $post->commentscount;
    
    $table = $this->thistable;
    $items = $this->select("$table.post = $this->pid and $table.status = 'approved'",
    "order by $table.posted asc limit $from, $count");
    
    $args = targs::instance();
    $args->from = $from;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $lang = tlocal::instance('comment');
    $theme = ttheme::instance();
    $i = 1;
    foreach ($items as $id) {
      $comment->id = $id;
      $args->class = (++$i % 2) == 0 ? $class1 : $class2;
      $result .= $theme->parsearg($tml, $args);
    }
    return $result;
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
    extract($this->data);
    $this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));
    
    $this->getdb($this->rawtable)->UpdateAssoc(array(
    'id' => $id,
    'modified' => sqldate(),
    'rawcontent' => $rawcontent,
    'hash' => md5($rawcontent)
    ));
  }
  
  public function getauthorlink() {
    $manager = tcommentmanager::instance();
    if ($manager->hidelink || ($this->url == '') || !$manager->checktrust($this->trust)) return $this->name;
    $rel = $manager->nofollow ? 'rel="nofollow noindex"' : '';
    if ($manager->redir) {
      return "<a $rel href=\"" . litepublisher::$options->url . "/comusers.htm" . litepublisher::$options->q . "id=$this->id\">$this->name</a>";
    } else {
      return "<a $rel href=\"$this->url\">$this->name</a>";
    }
  }
  
  public function getdate() {
    $theme = ttheme::instance();
    return tlocal::date($this->posted, $theme->comment->dateformat);
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
  
}//class

?>