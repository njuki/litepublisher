<?php

class TSubscribe extends TItems {
 //public $fromemail;
 
 public $title;
 public $formresult;
 private $user;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'subscribe';
  $this->CacheEnabled = false;
  $this->Data['fromemail'] = '';
  $this->Data['SubscribtionEnabled'] = true;
 }
 
 public function SetSubscribtionEnabled($value) {
  if ($this->SubscribtionEnabled != $value) {
   $this->Data['SubscribtionEnabled'] = $value;
   $this->Save();
   $CommentManager = &TCommentManager::Instance();
   if ($value) {
    $CommentManager->Lock();
    $CommentManager->Added = $this->SendMailToSubscribers;
    $CommentManager->Approved = $this->SendMailToSubscribers;
    $CommentManager->Deleted = $this->CommentDeleted;
    $CommentManager->Unlock();
   } else {
    $CommentManager->UnsubscribeClass($this);
   }
  }
 }
 
 public function CommentDeleted($id) {
  unset($this->items[$id]);
  $this->Save();
 }
 
 public function Request($param) {
  TLocal::LoadLangFile('admin');
  $lang = &TLocal::Instance();
  $lang->section = $this->basename;
  $this->title = $lang->title;
  $this->formresult = '';
  if (isset($_POST) && (count($_POST) > 0)) {
   if (get_magic_quotes_gpc()) {
    foreach ($_POST as $name => $value) {
     $_POST[$name] = stripslashes($_POST[$name]);
    }
   }
   $this->formresult= $this->ProcessForm();
  }
 }
 
 public function GetTemplateContent() {
  global $Options;
  $html= &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  eval('$result = "' . $html->title . '\n";');
  $result .= $this->formresult;
  
  $Users = &TCommentUsers::Instance();
  if ($this->user = $Users->GetItemFromCookie($_GET['userid'])) {
   if (count($this->user['subscribe']) == 0) {
    eval('$result .=  "' . $html->nosubscribtions . '\m";');
   } else {
    $email = $this->user['email'];
    eval('$result .="'. $html->formhead . '\n";');
    
    foreach ($this->user['subscribe'] as $postid) {
     $post = &TPost::Instance($postid);
     if ($post->status != 'published') continue;
     eval('$result .= "'. $html->formitem . '\n";');
    }
    eval('$result .= "'. $html->formfooter . '\n";');
   }
  } else {
   eval('$result .= "'. $html->notfound . '\m";');
  }
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  $result = '';
  $Users = &TCommentUsers::Instance();
  if ($this->user = $Users->GetItemFromCookie($_GET['userid'])) {
   $users->Lock();
   foreach ($_POST as $name => $value) {
    if (substr($name, 0, 7) == 'postid-') {
     $users->Unsubscribe($this->user['id'], $value);
    }
   }
   $users->Unlock();
   $html = &THtmlResource::Instance();
   $html->section = $this->basename;
   $lang = &TLocal::Instance();
   eval('$result .= "'. $html->unsubscribed . '\n";');
  }
  return $result;
 }
 
 public function Geturl() {
  global $Options;
  return $Options->url . '/comments/subscribe/' . $Options->q;
 }
 
 public function SendMailToSubscribers($id) {
  if (!$this->SubscribtionEnabled || (isset($this->items[$id]) && $this->items[$id])) return;
  
  $CommentManager = &TCommentManager::Instance();
  $item = $CommentManager->GetItem($id);
  if (isset($item['status']) || isset($item['type']))return;
  
  $this->items[$id] = true;
  $this->Save();
  
  global $Options;
  
  $comments = &TComments::Instance($item['pid']);
  $subscribers = &$comments->GetSubscribers();
  if (in_array($item['uid'], $subscribers)) {
   array_splice($subscribers,  array_search($item['uid'], $subscribers), 1);
  }
  
  if (count($subscribers) == 0) return;
  $comment = new TComment($comments);
  $comment->id = $id;
  
  $html = &THtmlResource::Instance();
  $html->section = 'moderator';
  $lang = &TLocal::Instance();
  
  eval('$subj = "'. $html->subject . '";');
  eval('$body = "' . $html->subscriberbody . '";');
  
  $url = $this->Geturl();
  
  $users = &TCommentUsers::Instance();
  foreach ($subscribers as $userid) {
   $user = $users->GetItem($userid);
   if (empty($user['email'])) continue;
 $link = "\n{$url}userid={$user['cookie']}\n";
   TMailer::SendMail($Options->name, $this->fromemail,  $user['name'], $user['email'],
   $subj, $body . $link);
  }
 }
 
}//class

?>