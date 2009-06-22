<?php

class TCommentForm extends TItems {
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename ='commentform';
  $this->CacheEnabled = false;
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function __set($name, $value) {
  if (isset($this->Data[$name])) {
   return $this->SetValue($name, $value);
  }
  return parent::set($name, $value);
 }
 
 public function SetValue($name, $value) {
  if ($this->Data[$name] != $value) {
   $this->Data[$name] = $value;
   $values = array();
   foreach ($this->Data['Fields'] as $name => $type) {
    $values[$name] = '';
   }
   foreach ($this->Data['Hidden'] as $name => $type) {
    $values[$name] = '';
   }
   
   $this->Data['values'] = $values;
   $this->Save();
   $this->Regenerate();
  }
 }
 
 public function Regenerate() {
  global $paths;
  $TemplateComment = &TTemplateComment::Instance();
  $form = $TemplateComment->GenerateCommentForm();
  file_put_contents($paths['cache']. 'commentform.php', $form);
  return $form;
 }
 
 public static function PrintForm($postid) {
  global $paths;
  $self = &GetInstance(__class__);
  $values = $self->GetAllFields();
  $values['postid'] = $postid;
  if (isset($values['subscribe'])) $values['subscribe'] = 'checked';
  $Result = '';
  
  if (!empty($_COOKIE["userid"])) {
   $Users = &TCommentUsers::Instance();
   if ($user = $Users->GetItemFromCookie($_COOKIE['userid'])) {
    if (isset($values['subscribe'])) {
     $values['subscribe'] = in_array($postid, $user['subscribe']) ? 'checked' : '';
     unset($user['subscribe']);
    }
    
    $values = $user + $values;
    
    //hold comment list
    $Comments = &TComments::Instance($postid);
    $items = &$Comments->GetHold($user['id']);
    if (count($items) > 0) {
     $comment = &new TComment($Comments);
     $TemplateComment = &TTemplateComment::Instance();
     $Result .= $TemplateComment->GetHoldList($items, $comment);
    }
   }
  }
  
  if (!($form = @file_get_contents($paths['cache']. 'commentform.php'))) {
   $form = $self->Regenerate();
  }
  $form = str_replace('"', '\"', $form);
  $self->BeforeForm($values);
  eval("\$form = \"$form\";");
  $Result .= $form;
  return $Result;
 }
 
 public function BeforeForm(&$values) {
  //spam protection
  $values['antispam'] = '_Value' . strtotime ("+1 hour");
 }
 
 public function CheckSpam(&$values) {
  $TimeKey = (int) substr($values['antispam'], strlen('_Value'));
  return time() < $TimeKey;
 }
 
 //
 public function Request($param) {
  global $Options;
  if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
   return "<?php
   @header('Allow: POST');
   @header('HTTP/1.1 405 Method Not Allowed');
   @header('Content-Type: text/plain');
   ?>";
  }
  
  $posturl = $Options->home;
  if (isset($_POST) && isset($_POST['postid'])) {
   if (get_magic_quotes_gpc()) {
    foreach ($_POST as $name => $value) {
     $_POST[$name] = stripslashes($_POST[$name]);
    }
   }
   
   $postid = (int) $_POST['postid'];
   if (!isset($_POST['confirmid'])) {
    if (!is_array($this->items)) {
     $this->items = array();
    } else {
     foreach ($this->items as $id => $item) {
      if ($item['date']+ 600 < time()) unset($this->items[$id]);
     }
    }
    
    $confirmid = md5($postid . secret. uniqid( microtime()));
    $values = $_POST;
    $values['date'] = time();
    $this->items[$confirmid] =$values;
    $this->Save();
    eval('$form ="' . TLocal::$data['commentform']['form'] . '\n";');
    return TTemplate::SimpleHtml($form);
   }
   
   $confirmid = $_POST['confirmid'];
   if (!isset($this->items[$confirmid])) {
    $error = TLocal::$data['commentform']['notfound'];
    return TTemplate::SimpleContent($error);
   }
   
   $values = $this->items[$confirmid];
   unset($this->items[$confirmid]);
   $this->Save();
   $values = $this->FilterValues($values);
   
   $Posts = &TPosts::Instance();
   if(!$Posts->ItemExists($values['postid']))  {
    $error = TLocal::$data['default']['postnotfound'];
    return TTemplate::SimpleContent($error);
   }
   
   $post = &TPost::Instance($values['postid']);
   if (!$this->ValidateValues($values, $error)) {
    return TTemplate::SimpleContent($error);
   }
   
   if (!$this->CanAdd($values, $post, $error)) {
    return TTemplate::SimpleContent($error);
   }
   
   $posturl =  $post->url;
   $users = &TCommentUsers ::Instance();
   $users->Lock();
   $userid = $users->Add($values['name'], $values['email'], $values['url']);
   $CommentManager = &TCommentManager::Instance();
   if (!$CommentManager->UserCanAdd( $userid)) {
    $error = TLocal::$data['comment']['toomany'];
    return TTemplate::SimpleContent($error);
   }
   $users->UpdateSubscribtion($userid, $post->id, isset($_POST['subscribe']));
   $users->Unlock();
   $usercookie = $users->GetCookie($userid);
   
   $CommentManager->AddToPost($post, $userid, $values['content']);
  }
  
  return "<?php
  @setcookie('userid', '$usercookie', time() + 30000000,  '/', false);
  @header('Location: http://$_SERVER[HTTP_HOST]$posturl');
  ?>";
 }
 
 protected function &GetAllFields() {
  $result = $this->Data['Fields'] + $this->Data['Hidden'];
  foreach ($result as $name => $type) {
   $result[$name] = '';
  }
  return $result;
 }
 
 public function FilterValues(&$values) {
  $result = &$this->GetAllFields();
  foreach ($result as $name => $defval) {
   if (isset($values[$name]) ) {
    $result[$name]  =  trim($values[$name]);
   }
  }
  
  if (isset($fields['name'])) {
   $result['name'] = strip_tags($result['name']);
  }
  $result['content'] = trim($values['content']);
  
  return $result;
 }
 
 public function ValidateValues(&$values, &$error) {
  $lang = TLocal::$data['comment'];
  if (!$this->CheckSpam($values))  {
   $error = $lang['spamdetected'];
   return false;
  }
  
  if (empty($values['content'])) {
   $error = $lang['emptycontent'];
   return false;
  }
  
  if (empty($values['name'])) {
   $error = $lang['emptyname'];
   return false;
  }
  
  if (!TContentFilter::ValidateEmail($values['email'])) {
   $error = $lang['invalidemail'];
   return false;
  }
  
  return true;
 }
 
 public function CanAdd(&$values, &$post, &$error) {
  $lang = &TLocal::Instance();
  $lang->section = 'comment';
  if (!$post->commentsenabled) {
   $error = $lang->commentsdisabled;
   return false;
  }
  
  if ($post->status != 'published')  {
   $error = $lang->commentondraft;
   return false;
  }
  
  //check duplicates
  $comments = &$post->comments;
  if ($comments->IndexOfRawContent($values['content']) >= 0) {
   $error = $lang->duplicate;
   return false;
  }
  
  return true;
 }
 
}//class

?>