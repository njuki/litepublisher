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

  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function delete($id) {
$this->db->setvalue($id, 'status', 'deleted');
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

public function getraw() {
return $this->getdb($this->rawtable);
}

  public function GetCountApproved() {
return $this->db->getcount("post = $this->pid and status = 'approved'");
}

public function getcontent() {
return $this->getcontentwhere('status', '');
}

public function getholdcontent($idauthor) {
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
    $comment = new TComment($this);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->comments->comment;
    $i = 1;
$res->setFetchMode (PDO::FETCH_ASSOC);
foreach ($res as $data) {
      $comment->data = $data;
      $args->class = (++$i % 2) == 0 ? $tml->class1 : $tml->class2;
$result .= $theme->parsearg($tml, $args);
    }

if ($result == '') return '';
    return sprintf($theme->content->post->templatecomments->comments, $result, $from + 1);    
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