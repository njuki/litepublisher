<?php

class TComments extends AbstractCommentManager {

//$postid ingnored
  public static function instance($postid = 0) {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->rawtable = 'rawcomments';
  }

public function load() { return true; }
public function save() { retrn true; }
  
  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function PostDeleted($postid) {
$this->db->update("status = 'deleted'", "post = $postid");
/*
$this->getdb($this->rawtable)->delete("id in (select id from $this->thistable where post = $postid)");
$this->db->delete("post = postid");
*/
}
  
  public function add($postid, $name, $email, $url, $content) {
    $users = TCommentUsers ::instance();
    $author = $users->add($name, $email, $url);
    $filter = TContentFilter::instance();
$result =$this->db->InsertAssoc(array(
'post' => $postid,
'parent' => 0,
'author' => $author,
'posted' => sqlnow(),
'content' =>$filter->GetCommentContent($Content),
'status' => $this->CreateStatus($author, $content),
'pingback' => 'false'
));
$this->getdb($this->rawtable)->InsertAssoc(array('id' => $result, 'rawcontent' => $content));
$this->DoAdded($result);
return $result;
  }
  
  public function AddPingback(tpost $post, $url, $title) {
    $users = TCommentUsers::instance();
    $userid = $users->add($title, '', $url);

    $date = $comments->Create($id, $userid, '', 'hold', 'pingback');
    
$result = $this->db->InsertAssoc(array(
    'author' => $userid,
    'post' => ($postid,
    'posted' => sqldate(),
    'status' => 'hold',
    'type' => 'pingback'
    ));
//no add to raw
    $this->DoAdded($result);
  }
  
  public function delete($id) {
$this->db->setvalue($id, 'status', 'deleted');
    
      $this->deleted($id);
      $this->DoChanged($postid);
  }
  
  public function setstatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
$this->db->setvalue($id, 'status', $value);
    $this->DoChanged($item['pid']);
  }
  
  public function gethold($author) {
return $this->db->res2array($this->db->select("author = $author and status = 'hold' and pingback = false"));
}

  public function getholditems() {
return $this->db->res2array($this->db->select("status = 'hold' and pingback = false"));
  }

//from files version
  public function IndexOfRawContent($s) {
$id = $this->getdb('rawcomments')->findid('rawcontent', $s);
return $id ? $id : -1;
  }
  
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
    
    $authors = TCommentUsers ::instance();
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