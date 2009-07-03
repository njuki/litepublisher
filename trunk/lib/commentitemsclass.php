<?php

class TComments extends TItems {
 public $postid;
 private static $Instances;
 
 public function GetBaseName() {
  return 'posts'.  DIRECTORY_SEPARATOR . $this->postid . DIRECTORY_SEPARATOR . 'comments';
 }
 
 public static function &Instance($postid) {
  $ClassName = __class__;
  if (!isset(self::$Instances)) self::$Instances = array();
  if (!isset(self::$Instances[$postid]))  {
   self::$Instances[$postid]  = &new $ClassName();
   $self = &self::$Instances[$postid];
   $self->postid = $postid;
   $self->Load();
  }
  return self::$Instances[$postid];
 }
 
 public static function &GetComment($postid, $id) {
  $self = &self::Instance($postid);
  $result = &new TComment($self);
  $result->id = $id;
  return $result;
 }
 
 public function Create($id, $userid,  $Content,$status = 'hold',  $type = '') {
  $date = time();
  $ContentFilter = &TContentFilter::Instance();
  $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
  
  $this->items[$id] = array(
  'id' => $id,
  'uid' => $userid,
  'date' => $date,
  'status' => $status,
  'type' => $type,
  'content' => $ContentFilter ->GetCommentContent($Content),
  'rawcontent' =>  $Content,
  'ip' =>$ip
  );
  $this->Save();
  return $date;
 }
 
 public function SetStatus($id, $value) {
  $this->SetValue($id, 'status', $value);
  $this->Save();
 }
 
 public function SetContent($id, $value) {
if (isset($this->items[$id])) {
  $ContentFilter = &TContentFilter::Instance();
$this->items[$id]['content'] = $ContentFilter ->GetCommentContent($value);
$this->items[$id]['rawcontent'] =  $value;
$this->Save();
}
 }
 
 public function &GetApproved($type = '') {
  $Result = array();
  foreach ($this->items as $id => $item) {
   if (($item['status'] == 'approved')  && ($type == $item['type'])) {
    $Result[$id] = $item['date'];
   }
  }
  asort($Result);
  return  $Result;
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
 
 public function &GetHold($userid) {
  $Result = array();
  foreach ($this->items as $id => $item) {
   if (($item['status'] == 'hold')  && ($userid == $item['uid'])) {
    $Result[$id] = $item['date'];
   }
  }
  asort($Result);
  return  $Result;
 }
 
 public function GetUserInfo($id) {
  $Users = &TCommentUsers::Instance();
  return  $Users->GetItem($this->items[$id]['uid']);
 }
 
 public function IndexOfRawContent($s) {
  return $this->IndexOf('rawcontent', $s);
 }
 
 public function Getcontent($id) {
  return $this->items[$id]['content'];
 }
 
 public function HasPingback($url) {
  $users = &TCommentUsers::Instance();
  $userid = $users->IndexOf('url', $url);
  if ($userid == -1) return false;
  $id = $this->IndexOf('uid', $userid);
  if ($id == -1) return false;
  return $this->items[$id]['type'] == 'pingback';
 }
 
 public function &GetSubscribers() {
  $result = array();
  $users = &TCommentUsers::Instance();
  foreach ($this->items as $id => $item) {
   if (($item['status'] == 'approved') && ($item['type'] == '') && $users->Subscribed($item['uid'], $this->postid)) {
    if (!in_array($item['uid'], $result)) $result[] = $item['uid'];
   }
  }
  return $result;
 }
 
}//class

//wrapper for simple acces to single comment
class TComment {
 public $id;
 public $Owner;
 
 public function __construct(&$Owner = null) {
  $this->Owner = &$Owner;
 }
 
 public function __get($name) {
  if (method_exists($this,$get = "Get$name")) {
   return  $this->$get();
  }
  return $this->Owner->GetValue($this->id, $name);
 }
 
 public function __set($name, $value) {
if ($name == 'content') {
$this->Owner->SetContent($this->id, $value);
 } else {
$this->Owner->SetValue($this->id, $name, $value);
}
 }
 
 public function Save() {
  $this->Owner->Save();
 }
 
 public function Getname() {
  $UserInfo = $this->Owner->GetUserInfo($this->id);
  return $UserInfo['name'];
 }
 
 public function Getip() {
  $UserInfo = $this->Owner->GetUserInfo($this->id);
  return $UserInfo['ip'][0];
 }
 
 public function Getauthorlink() {
  if ($this->type == 'pingback') {
 return "<a href=\"{$this->website}\">{$this->name}</a>";
  }
  
  $authors = &TCommentUsers ::Instance();
  return $authors->GetLink($this->Owner->items[$this->id]['uid']);
 }
 
 public function Getlocaldate() {
  return TLocal::date($this->date);
 }
 
 public function Getlocalstatus() {
  return TLocal::$data['commentstatus'][$this->status];
 }
 
 public function  Gettime() {
  return date('H:i', $this->date);
 }
 
 public function Getwebsite() {
  $users = &TCommentUsers::Instance();
  return $users->GetValue($this->Owner->GetValue($this->id, 'uid'), 'url');
 }
 
 public function Getemail() {
  $UserInfo = $this->Owner->GetUserInfo($this->id);
  return $UserInfo ['email'];
 }
 
 public function Geturl() {
  global $Options;
  $post = &TPost::Instance($this->Owner->postid);
  return "$Options->url$post->url#comment-$this->id";
 }
 
 public function Getposttitle() {
  $post = &TPost::Instance($this->Owner->postid);
  return $post->title;
 }
 
}

?>