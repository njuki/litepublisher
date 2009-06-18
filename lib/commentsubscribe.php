<?php

class TSubscribe extends TItems {
 //public $fromemail;
 
 public $title;
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
  $this->title = TLocal::$data['subscribe']['title'];
 }
 
 public function GetTemplateContent() {
  $Users = &TCommentUsers::Instance();
  if ($this->user = $Users->GetItemFromCookie($_GET['userid'])) {
   if (isset($_POST) ) $this->Unsubscribe();
   $result = 	'<h2 class="center">' . TLocal::$data['subscribe']['title'] . "</h2>\n";
   if (count($this->user['subscribe']) > 0) {
    $result .=    '<p><strong>' . $this->user['email'] . '</strong> ' . TLocal::$data['subscribe']['help'] . "</p>\n";
    $result .= $this->GetForm();
   } else {
    $result .=    '<p class="center">'. TLocal::$data['subscribe']['empty'] . "</p>\n";
   }
   return $result;
  } else {
   return 		'<h2 class="center">' . TLocal::$data['default']['notfound'] . '</h2>
   <p class="center">'. TLocal::$data['default']['nocontent'] . '</p>';
  }
 }
 
 public function Unsubscribe() {
  $users = &TCommentUsers::Instance();
  $users->Lock();
  foreach ($_POST as $name => $value) {
   if (substr($name, 0, 7) == 'postid-') {
    $users->Unsubscribe($this->user['id'], $value);
   }
  }
  $users->Unlock();
 }
 
 public function GetForm() {
  global $Options;
  $result = '<form method="post" action="" >';
  foreach ($this->user['subscribe'] as $postid) {
   $post = &TPost::Instance($postid);
   if ($post->status != 'published') continue;
   $result .="\n<p><input type=\"checkbox\" name=\"postid-$postid\" id=\"postid-$postid\" value=\"$postid\" />
   <label for=\"postid-$postid\"><a href=\"$Options->url$post->url\"><strong>$post->title</strong></a></label></p>\n";
  }
  
  $result .= '<p><input name="submit" type="submit" id="submit" value="' . TLocal::$data['default']['unsubscribe'] . '" />
  </form>';
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
  $body = TLocal::$data['subscribe']['body'];
  eval('$body = "' . $body . '";');
  
  $subj = TLocal::$data['subscribe']['subject'];
  eval('$subj = "'. $subj . '";');
  
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