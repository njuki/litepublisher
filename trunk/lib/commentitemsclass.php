<?php

class tcomments extends TItems implements IComments {
  public $pid;
  private static $instances;
  
  public static function instance($pid) {
    if (!isset(self::$instances)) self::$instances = array();
    if (!isset(self::$instances[$pid]))  {
$class = __class__;
$self = new $class();
      self::$instances[$pid]  = $self;
      $self->pid = $pid;
      $self->load();
    }
    return $self;
  }
  
  public static function getcomment($pid, $id) {
    $self = self::instance($pid);
    $result = new tcomment($self);
    $result->id = $id;
    return $result;
  }

  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments';
  }
  
  public function insert($id, $userid,  $Content, $status,  $type) {
    $filter = TContentFilter::instance();
    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    
    $this->items[$id] = array(
    'id' => $id,
    'uid' => $userid,
    'posted' => time(),
    'status' => $status,
    'type' => $type,
    'content' => $filter->GetCommentContent($Content),
    'rawcontent' =>  $Content,
    'ip' =>$ip
    );
    $this->save();
    return $id;
  }
  
  public function setstatus($id, $value) {
    $this->setvalue($id, 'status', $value);
    $this->save();
  }
  
  public function getapproved($type = '') {
    $Result = array();
    foreach ($this->items as $id => $item) {
      if (($item['status'] == 'approved')  && ($type == $item['type'])) {
        $Result[$id] = $item['posted'];
      }
    }
    asort($Result);
    return  array_keys($Result);
  }
  
  public function GetCountApproved() {
    $result = 0;
    foreach ($this->items as $id => $item) {
      if (($item['status'] == 'approved')  && ('' == $item['type'])) {
        $result++;
      }
    }
    return $result;
  }
  
  public function gethold($userid) {
    $Result = array();
    foreach ($this->items as $id => $item) {
      if (($item['status'] == 'hold')  && ($userid == $item['uid'])) {
        $Result[$id] = $item['date'];
      }
    }
    asort($Result);
    return  array_keys($Result);
  }
  
  public function IndexOfRawContent($s) {
    return $this->IndexOf('rawcontent', $s);
  }
  
 public function haspingback($url) {
    $users = TCommentUsers::instance();
    $userid = $users->IndexOf('url', $url);
    if ($userid == -1) return false;
    $id = $this->IndexOf('uid', $userid);
    if ($id == -1) return false;
    return $this->items[$id]['type'] == 'pingback';
  }
  
  public function &GetSubscribers() {
    $result = array();
    $users = &TCommentUsers::instance();
    foreach ($this->items as $id => $item) {
      if (($item['status'] == 'approved') && ($item['type'] == '') && $users->Subscribed($item['uid'], $this->pid)) {
        if (!in_array($item['uid'], $result)) $result[] = $item['uid'];
      }
    }
    return $result;
  }
  
}//class

//wrapper for simple acces to single comment
class TComment {
  public $id;
  public $owner;
  
  public function __construct($owner = null) {
    $this->owner = $owner;
  }
  
  public function __get($name) {
    if (method_exists($this,$get = "get$name")) {
      return  $this->$get();
    }
    return $this->owner->getvalue($this->id, $name);
  }
  
  public function __set($name, $value) {
    if ($name == 'content') {
      $this->setcontent($value);
    } else {
      $this->owner->setvalue($this->id, $name, $value);
    }
  }
  
  public function save() {
    $this->owner->save();
  }

  private function setcontent($value) {
      $filter = TContentFilter::instance();
      $this->owner->items[$this->id]['content'] = $filter->GetCommentContent($value);
      $this->owner->items[$this->id]['rawcontent'] =  $value;
      $this->save();
    }
  
private function getuser($id) {
    $Users = TCommentUsers::instance();
    return  $Users->getitem($this->owner->items[$id]['uid']);
  }
  
  public function getname() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['name'];
  }

    public function getemail() {
    $userinfo = $this->getuser($this->id);
    return $userinfo ['email'];
  }
  
  public function getwebsite() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['url'];
}

   public function getip() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['ip'][0];
  }
  
  public function getauthorlink() {
    if ($this->type == 'pingback') {
  return "<a href=\"{$this->website}\">{$this->name}</a>";
    }
    
    $authors = TCommentUsers ::instance();
    return $authors->getlink($this->owner->items[$this->id]['uid']);
  }
  
  public function getlocaldate() {
    return tlocal::date($this->date);
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
  
}

?>