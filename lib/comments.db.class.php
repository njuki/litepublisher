<?php

class tcomments extends AbstractCommentManager implements icomments, icommentmanager  {
public $rawtable;
private $pid;

  public static function instance($pid = 0) {
    $result = getinstance(__class__);
$result->pid = $pid;
return $result;
  }
  
  protected function create() {
    parent::create();
$this->table = 'comments';
$this->rawtable = 'rawcomments';
  }

public function load() { return true; }
public function save() { retrn true; }
  
  public function addcomment($pid, $uid, $content) {
    $filter = TContentFilter::instance();
$result =$this->db->add(array(
'post' => $pid,
'parent' => 0,
'author' => $uid,
'posted' => sqldate(),
'content' =>$filter->GetCommentContent($Content),
'status' => $classes->spamfilter->createstatus($uid, $content),
'pingback' => 'false'
));

$this->getdb($this->rawtable)->add(array(
'id' => $result, 
'created' => sqldate(),
'modified' => sqldate(),
'rawcontent' => $content
));
$this->doadded($result);
return $result;
  }

 public function addpingback(tpost $post, $url, $title) {
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
    $this->doadded($result);
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

public function getitems($where = '') {
return $this->db->getcount($where);
}

public function getitems($where, $from, $count) {
$db = $this->db;
$res = $db->query("select $db->comments.*, $db->comusers.name, $db->comusers.email, $db->comusers.url from $db->comments, $db->comusers
where  $where sort by $db->comments.posted asc limit $from, $count");
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
where $db->post = $this->pid and pingback = true and $db->comusers.id = $db->comments.author and $db->comusers->url = $url limit 1")) && ($r = $res->fetch()) return true;
return false;
{

}//class

class tcomment extends TDataClass {

  public function __construct($id) {
parent::__construct();
$this->table = 'comments';
$this->setid($id);
}

public function setid($id) {
$table = $this->thistable;
$authors = $this->db->prefix . 'comusers';
$this->data= $this->db->queryassoc("select $table.*, $authors.name, $authors.email, $authors.url $authors.ip from $table, $authors
where $table.id = $id, $authors.id = $table.author limit 1");
  }
  
  public function save() {
extract($this->data);
$this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));
  }
  
 public function getauthorlink() {
    if ($this->pingback == '1') {
  return "<a href=\"{$this->website}\">{$this->name}</a>";
    }
    
    $authors = tcomusers ::instance();
    return $authors->getlink($this->author);
  }
  
  public function Getlocaldate() {
    return TLocal::date($this->date);
  }
  
  public function Getlocalstatus() {
    return tlocal::$data['commentstatus'][$this->status];
  }

public function getdate() {
return strtotime($this->posted);
}

public function setdate($date) {
$this->data['posted'] = sqldate($date);
}
  
  public function  gettime() {
    return date('H:i', $this->date);
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
?>