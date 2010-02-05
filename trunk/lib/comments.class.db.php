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
    $result->pid = $pid;
    return $result;
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'comments';
    $this->rawtable = 'rawcomments';
    $this->addevents('edited');
  }
  
  public function add($idauthor, $content, $status) {
    $filter = tcontentfilter::instance();
    $filtered = $filter->filtercomment($content);
    
    $item = array(
    'post' => $this->pid,
    'parent' => 0,
    'author' => $idauthor,
    'posted' => sqldate(),
    'content' =>$filtered,
    'status' => $status
    );
    
    $id =$this->db->add($item);
    $item['rawcontent'] = $content;
    $this->items[$id] = $item;
    
    $this->getdb($this->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $content,
    'hash' => md5($content)
    ));
    
    return $id;
  }
  
  public function edit($id, $idauthor, $content) {
    try {
      $item = $this->getitem((int) $id);
    } catch (Exception $e) {
      return false;
    }
    $filter = tcontentfilter::instance();
    $item['author'] = (int)$idauthor;
    $item['content'] =$filter->filtercomment($content);
    
    $this->db->updateassoc($item);
    
    $item['rawcontent'] = $content;
    $this->items[$id] = $item;
    
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
  
  public function getcount($where = '') {
    return $this->db->getcount($where);
  }
  
  public function getitems($where) {
    $db = $this->db;
    $res = $db->query("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
    where  $where");
    return $res->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getraw() {
    return $this->getdb($this->rawtable);
  }
  
  public function getapprovedcount() {
    return $this->db->getcount("post = $this->pid and status = 'approved'");
  }
  
  public function getcontent() {
    global $options, $urlmap;
    $result = $this->getcontentwhere('approved', '');
    if ($options->admincookie) {
      $theme = ttheme::instance();
      tlocal::loadlang('admin');
      $result .= $theme->parse($theme->content->post->templatecomments->comments->hold);
      $post = tpost::instance($this->pid);
      if ($post->commentpages == $urlmap->page) {
        $result .= $this->getcontentwhere('hold', '');
      } else {
        //добавить пустой список задержанных
        $commentsid = $theme->content->post->templatecomments->comments->commentsid;
        $tml = $theme->content->post->templatecomments->comments->__tostring();
        $tml = str_replace("id=\"$commentsid\"", "id=\"hold$commentsid\"", $tml);
        $tml = str_replace('<a name="comments"', '<a name="holdcomments"', $tml);
        $result .= sprintf($tml, '', 1);
      }
      
      $args = targs::instance();
      $args->comments = $result;
      $result = $theme->parsearg($theme->content->post->templatecomments->moderateform, $args);
    }
    return $result;
  }
  
  public function getholdcontent($idauthor) {
    global $options;
    if ($options->admincookie) return '';
    return $this->getcontentwhere('hold', "and $this->thistable.author = $idauthor");
  }
  
  private function getcontentwhere($status, $whereauthor) {
    global $db, $options, $urlmap, $comment;
    $result = '';
    $post = tpost::instance($this->pid);
    if ($status == 'approved') {
      $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
      $count = $options->commentpages  ? $options->commentsperpage : $post->commentscount;
    } else {
      $from = 0;
      $count = $options->commentsperpage;
    }
    
    $comusers = tcomusers::instance();
    $authors = $comusers->thistable;
    $table = $this->thistable;
    $res = $db->query("select $table.*, $authors.name, $authors.email, $authors.url, $authors.trust from $table, $authors
    where $table.post = $this->pid and $table.status = '$status' $whereauthor and $authors.id = $table.author
    order by $table.posted asc limit $from, $count");
    
    $args = targs::instance();
    $args->from = $from;
    $comment = new tcomment(0);
    $lang = tlocal::instance('comment');
    $theme = ttheme::instance();
    if ($options->admincookie) {
      tlocal::loadlang('admin');
      $moderate =$theme->content->post->templatecomments->comments->comment->moderate;
    } else {
      $moderate = '';
    }
    $tml = str_replace('$moderate', $moderate, $theme->content->post->templatecomments->comments->comment);
    
    $i = 1;
    $class1 = $theme->content->post->templatecomments->comments->comment->class1;
    $class2 = $theme->content->post->templatecomments->comments->comment->class2;
    $res->setFetchMode (PDO::FETCH_ASSOC);
    foreach ($res as $data) {
      $comment->data = $data;
      $args->class = (++$i % 2) == 0 ? $class1 : $class2;
      $result .= $theme->parsearg($tml, $args);
    }
    
    
    $tml = $theme->content->post->templatecomments->comments->__tostring();
    if ($status == 'hold') {
      $tml = str_replace('<a name="comments"', '<a name="holdcomments"', $tml);
      $commentsid = $theme->content->post->templatecomments->comments->commentsid;
      $tml = str_replace("id=\"$commentsid\"", "id=\"hold$commentsid\"", $tml);
    }
    
    if (!$options->admincookie) {
      if ($result == '') return '';
    }
    return sprintf($tml, $result, $from + 1);
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
    $table = $this->thistable;
    $authors = $this->db->comusers;
    $this->data= $this->db->queryassoc("select $table.*,
    $authors.name, $authors.email, $authors.url, $authors.ip
    from $table, $authors
    where $table.id = $id and $authors.id = $table.author limit 1");
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
    global $options;
    $manager = tcommentmanager::instance();
    if ($manager->hidelink || ($this->url == '') || !$manager->checktrust($this->trust)) return $this->name;
    $rel = $manager->nofollow ? 'rel="nofollow noindex"' : '';
    if ($manager->redir) {
    return "<a $rel href=\"$options->url/comusers.htm{$options->q}id=$this->id\">$this->name</a>";
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