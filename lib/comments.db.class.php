<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcomments extends TAbstractCommentManager implements icomments, icommentmanager  {
public $rawtable;
private $pid;

  public static function instance($pid = 0) {
    $result = getinstance(__class__);
$result->pid = $pid;
return $result;
  }
  
  protected function create() {
    parent::create();
$this->dbversion = true;
$this->table = 'comments';
$this->rawtable = 'rawcomments';
  }

  protected function dochanged($id, $idpost) {
$this->getdb('posts')->setvalue($idpost, 'commentscount', $this->db->getcount("post = $postid and status = 'approved' and pingback = false"));
parent::dochanged($id, $idpost);
$comusers = tcomusers::instance();
$comusers->updatetrust($item['author']);

}

    public function addcomment($pid, $uid, $content) {
global $classes;
    $filter = TContentFilter::instance();
$filtered = $filter->GetCommentContent($content);
$status = $classes->spamfilter->createstatus($uid, $content);
$result =$this->db->add(array(
'post' => $pid,
'parent' => 0,
'author' => $uid,
'posted' => sqldate(),
'content' =>$filtered,
'status' => $status,
'pingback' => 'false'
));

$this->getdb($this->rawtable)->add(array(
'id' => $result, 
'created' => sqldate(),
'modified' => sqldate(),
'rawcontent' => $content,
'hash' => md5($content)
));
$this->doadded($result, $pid);
return $result;
  }

public function addpingback($pid, $url, $title) {
    $comusers = tcomusers::instance();
    $uid = $comusers->add($title, '', $url);

$result = $this->db->add(array(
'parent' => 0,
    'author' => $uid,
    'post' => $pid,
    'posted' => sqldate(),
    'status' => 'hold',
'pingback' => 'true'
    ));
//no add to raw
    $this->doadded($result, $pid);
  }
  
  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function delete($id) {
$this->db->setvalue($id, 'status', 'deleted');
    
      $this->deleted($id);
      $this->dochanged($postid);
  }

  public function postdeleted($postid) {
$this->db->update("status = 'deleted'", "post = $postid");
}
  
    public function setstatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
$this->db->setvalue($id, 'status', $value);
    $this->dochanged($item['pid']);
  }
  
  public function gethold($author) {
return $this->db->idselect("post = $this->pid and author = $author and status = 'hold' and pingback = false");
}

public function getcount($where = '') {
return $this->db->getcount($where);
}

public function getitems($where, $from, $count) {
$db = $this->db;
$res = $db->query("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
where  $where order by $db->comments.posted asc limit $from, $count");
return $res->fetchAll(PDO::FETCH_ASSOC);
}

  public function IndexOfRawContent($s) {
$id = $this->getdb('rawcomments')->findid('rawcontent', $s);
return $id ? $id : -1;
  }
  
 public function haspingback($url) {
$db = $this->db;
$url = $db->quote($url);
if (($res = $this->db->query("select $db->comments.id from$db->comments, $db->comusers
where $db->post = $this->pid and pingback = true and $db->comusers.id = $db->comments.author and $db->comusers->url = $url limit 1")
 && ($r = $res->fetch()))) return true;
return false;
}

  public function GetCountApproved() {
return $this->db->getcount("post = $this->pid and status = 'approved' and pingback = false");
}

public function hasapproved($uid, $count) {
return $count == $this->getcount("author = $uid and status = 'approved' limit $count");
}

  public function getapproved($idpost, $from, $count) {
global $db;
$comusers = tcomusers::instance();
$authors = $comusers->thistable;
$table = $this->thistable;
return $db->queryassoc("select $table.*, $authors.name, $authors.email, $authors.url, $authors.trust from $table, $authors
where $table.post = $this->pid and $table.status = 'approved' and $table.pingback = 'false' and $authors.id = $table.author
order by $table.posted asc limit $from, $count");
}

public function getpingbacks() {
global $db;
$comusers = tcomusers::instance();
$authors = $comusers->thistable;
$table = $this->thistable;
return $db->queryassoc("select $table.*, $authors.name, $authors.url, $authors.trust from $table, $authors
where $table.post = $this->pid and $table.status = 'approved' and $table.pingback = 'true' and $authors.id = $table.author
order by $table.posted desc");
}

}//class

class tcomment extends tdata {

  public function __construct($id) {
if (!isset($id)) return false;
parent::__construct();
$this->table = 'comments';
if (is_int($id)) $this->setid($id);
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
  }
  
 public function getauthorlink() {
    if ($this->pingback == '1') {
  return "<a href=\"{$this->website}\">{$this->name}</a>";
    }
    
    $comusers = tcomusers ::instance();
    return $comusers->getlink($this->name, $this->url, $this->trust);
  }
  
  public function getdate() {
$theme = ttheme::instance();
    return TLocal::date($this->posted, $theme->comment->dateformat);
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
  
}//class

?>