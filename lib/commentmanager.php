<?php

class TCommentManager extends TItems {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'commentmanager';
  $this->AddEvents('Edited', 'Changed', 'Approved');
  $this->Data['recentcount'] =  7;
  $this->Data['SendNotification'] =  true;
 }
 
 public function &Getcomment($id) {
  return TComments::GetComment($this->items[$id]['pid'], $id);
 }
 
 public function SetSendNotification($value) {
  if ($this->SendNotification != $value) {
   $this->Data['SendNotification'] = $value;
   $this->Save();
  }
 }
 
 public function Setrecentcount($value) {
  if ($value != $this->recentcount) {
   $this->Data['recentcount'] = $value;
   $this->Save();
  }
 }
 
 public function GetWidgetContent($id) {
  global $Options, $Template;
  $result = $Template->GetBeforeWidget('recentcomments');
  
  $count = $this->recentcount;
  if ($item = end($this->items)) {
   $users = &TCommentUsers::Instance();
   $onrecent = TLocal::$data['comment']['onrecent'];
   do {
    $id = key($this->items);
    if (!isset($item['status']) && !isset($item['type']) ) {
     $count--;
     $post = &TPost::Instance($item['pid']);
     $content = $post->comments->GetValue($id, 'content');
     $content = TContentFilter::GetExcerpt($content, 120);
     $user = $users->GetItem($item['uid']);
     $result .= "\n\t<li><strong><a href=\"$Options->url$post->url#comment-$id\" title=\"$onrecent $post->title\">$user[name]</a></strong>: $content...</li>";
    }
   } while (($count > 0) && ($item  = prev($this->items)));
  }
  
  $result .= $Template->GetAfterWidget();
  return $result;
 }
 
 public function PostDeleted($postid) {
  $this->Lock();
  foreach ($this->items as  $id => $item) {
   if ($item['pid'] == $postid) {
    unset($this->items[$id]);
   }
  }
  $this->Unlock();
 }
 
 public function AddToPost(&$post, $userid, $content) {
  $id = ++  $this->lastid;
  $comments = &$post->comments;
  $status = $this->CreateStatus($userid, $content);
  $date = $comments->Create($id, $userid,  $content, $status);
  
  $this->items[$id] = array(
  //'id' => $id,
  'uid' => (int) $userid,
  'pid' => (int) $post->id,
  'date' => $date
  );
  if ($status != 'approved') $this->items[$id]['status'] = $status;
  $this->Save();
  $this->DoAdded($id);
 }
 
 protected function CreateStatus($userid, $content) {
  global $Options;
  if ($Options->DefaultCommentStatus == 'approved') return 'approved';
  if ($this->UserHasApproved($userid)) return  'approved';
  return 'hold';
 }
 
 public function AddPingback(&$post, $url, $title) {
  $id =++$this->lastid;
  $users = &TCommentUsers::Instance();
  $userid = $users->Add($title, '', $url);
  $comments = &$post->comments;
  $date = $comments->Create($id, $userid, '', 'hold', 'pingback');
  
  $this->items[$id] = array(
  //'id' => $id,
  'uid' => $userid,
  'pid' => (int) $post->id,
  'date' => $date,
  'status' => 'hold',
  'type' => 'pingback'
  );
  $this->Save();
  $this->DoAdded($id);
 }
 
 public function DoAdded($id) {
  $this->DoChanged($this->items[$id]['pid']);
  $this->CommentAdded($id);
  $this->Added($id);
 }
 
 public function HasUser($userid) {
  foreach ($this->items as $id => $item) {
   if ($userid == $item['uid'])  return true;
  }
  return false;
 }
 
 public function UserHasApproved($userid) {
  foreach ($this->items as $id => $item) {
   if (($userid == $item['uid']) && !isset($item['status'])) return true;
  }
  return false;
 }
 
 public function HasApprovedCount($userid, $count) {
  foreach ($this->items as $id => $item) {
   if (($userid == $item['uid']) && !isset($item['status'])) {
    if (--$count ==0) return true;
   }
  }
  return false;
 }
 
 public function Delete($id) {
  if (isset($this->items[$id])) {
   $this->Lock();
   $comments = &TComments::Instance($this->items[$id]['pid']);
   $comments->Delete($id);
   $postid = $this->items[$id]['pid'];
   $userid = $this->items[$id]['uid'];
   unset($this->items[$id]);
   $this->Unlock();
   
   if (!$this->HasUser($userid)) {
    $users = &TCommentUsers::Instance();
    $users->Delete($userid);
   }
   
   $this->Deleted($id);
   $this->DoChanged($postid);
   return true;
  }
  return false;
 }
 
 public function DoChanged($postid) {
  TTemplate::WidgetExpired($this);
  
  $post = &TPost::Instance($postid);
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->SetExpired($post->url);
  
  $this->Changed($postid);
 }
 
 public function SetStatus($id, $value) {
  if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
  $item = $this->items[$id];
  if ( (($value == 'approved') && !isset($item['status']))  || ($value == $item['status'])) return false;
  
  $comments = &TComments::Instance($item['pid']);
  $comments->SetStatus($id, $value);
  
  $this->Lock();
  if ($status == 'approved') {
   unset($this->items[$id]['status']);
   if (!isset($item['type'])) $this->Approved($id);
  } else {
   $this->items[$id]['status'] = $value;
  }
  $this->Unlock();
  $this->DoChanged($item['pid']);
 }
 
 public function UserCanAdd($userid) {
  $count = 0;
  $approved = 0;
  foreach($this->items as $id => $item) {
   if ($item['uid'] == $userid) {
    $count++;
    if (!isset($item['status']) ) $approved++;
   }
  }
  if ($count < 2) return true;
  if  ($approved ==0) return false;
  return true;
 }
 
 public function Getholditems() {
  $result = array();
  foreach($this->items as $id => $item) {
   if (!empty($item['status']) && ($item['status'] == 'hold')) {
    $result[$id] = $item;
   }
  }
  return $result;
 }
 
 public function CommentAdded($id) {
  global $Options;
  if (!$this->SendNotification) return;
  $comment = &$this->Getcomment($id);
  $html = &THtmlResource::Instance();
  $html->section = 'moderator';
  eval('$subject = "' . $html->subject . '";');
  eval('$body = "'. $html->body . '";');
  TMailer::SendMail($Options->name, $Options->fromemail,
  'admin', $Options->email,  $subject, $body);
 }
 
}//class

?>