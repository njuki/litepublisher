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
    parent::create();
$this->dbversion = true;
$this->table = 'comments';
$this->rawtable = 'rawcomments';
  }

    public function add($idauthor, $content, $status) {
    $filter = TContentFilter::instance();
$filtered = $filter->GetCommentContent($content);

$item = array(
'post' => $idpost,
'parent' => 0,
'author' => $idauthor,
'posted' => sqldate(),
'content' =>$filtered,
'status' => $status
);

$id =$this->db->add($item);
$item['rawcontent'] => $content;
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

  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function delete($id) {
$this->db->setvalue($id, 'status', 'deleted');
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


public function getcontent() {
    global $options, $urlmap, $comment;
    $result = '';
    $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
    if ($options->commentpages ) {
$args = targs::instance();
$args->from = $from;
    $items = $comments->getapproved($from, $options->commentpages ? $options->commentsperpage : $post->commentscount);


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