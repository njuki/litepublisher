<?php

class TComments extends AbstractCommentManager {

 
  public static function instance() {
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
  
 public function GetWidgetContent($id) {
    global $options;
    $template = template::instance();
    $result = '';
    $templ = isset($template->theme['widget']['recentcomment']) ? $template->theme['widget']['recentcomment'] :
    '<li><strong><a href="%1$s#comment-%2$s" title="%6$s %3$s">%4$s</a></strong>: %5$s...</li>';
    
      $onrecent = TLocal::$data['comment']['onrecent'];
$table =  $this->thistable;
$prefix = $this->db->prefix;
$authors = $prefix . 'commentsusers';
$poststable = $prefix . 'posts';
$urltable = $prefix . 'urlmap';
$res = $this->db->query("
select $table.*, $authors.name as name, $poststable.title as title, $urltable.url as posturl 
from $table, $authorstable, $poststable, $urltable
where $table.status = 'approved' and $table.pingback = 'false',
$authorstable.id = $table.author, $poststable.id = $table.post, $urlmap.class = 'tposts' and $urltable.arg = $table.post
sort by $table.created desc limit ".$this->options->recentcount); 
while ($row = $res->fetch()) {
         $content = TContentFilter::GetExcerpt($row['content'], 120);
          $result .= sprintf($templ, $options->url . $posturl, $row['id], $row['title'], $row['name'], $content, $onrecent);
        }
    
    return $result;
  }
  
  public function PostDeleted($postid) {
$this->getdb($this->rawtable)->delete("id in (select id from $this->thistable where post = $postid)");
$this->db->delete("post = postid");
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
  
  protected function CreateStatus($userid, $content) {
    global $options;
    if ($options->DefaultCommentStatus == 'approved') return 'approved';
    if ($this->UserHasApproved($userid)) return  'approved';
    return 'hold';
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
  
 public function hasauthor($author) {
if (($res = $this->db->select("author = $author limit 1")) && $res->fetch()) return true;
return false;
  }
  
  public function UserHasApproved($author) {
if (($res = $this->db->select("author = $author and status = 'approved' limit 1")) && $res->fetch()) return true;
    return false;
  }
  
  public function HasApprovedCount($author, $count) {
if (($res = $this->db->query(select count(author) as count from $this->thistable where author = $author and status = 'approved' limit $count")) && ($row = $res->fetch()) return $count ><= $row['count'];
    return false;
  }
  
  public function delete($id) {
$author = $this->db->getvalue($id, 'author');
$this->db->iddelete($id);

           if (!$this->hasauthor($author)) {
        $users = TCommentUsers::instance();
        $users->iddelete($author);
      }
      
      $this->deleted($id);
      $this->DoChanged($postid);
  }
  
  public function setstatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
$this->db->setvalue($id, 'status', $value);
    $this->DoChanged($item['pid']);
  }
  
  public function UserCanAdd($userid) {
$res = $this->db->query("select count(id) as count from $this->thistable where author = $author 
union select count(id) as approved from $this->thistable where author = $author  and status = 'approved'");
extract($res->fetch());
    if ($count < 2) return true;
    if  ($approved ==0) return false;
    return true;
  }
  
  public function getholditems() {
return $this->db->res2array($this->db->select("status = 'hold' and pingback = false"));
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