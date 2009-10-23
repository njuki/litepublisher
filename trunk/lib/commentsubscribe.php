<?php

class tsubscribers extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->Data['fromemail'] = '';
    $this->Data['SubscribtionEnabled'] = true;
    $this->Data['locklist'] = '';
  }
  
  public function SetSubscribtionEnabled($value) {
global $classes;
    if ($this->SubscribtionEnabled != $value) {
      $this->Data['SubscribtionEnabled'] = $value;
      $this->save();
      $manager = $classes->commentmanager;
      if ($value) {
        $manager->lock();
        $manager->added = $this->SendMailToSubscribers;
        $manager->approved = $this->SendMailToSubscribers;
        $manager->deleted = $this->CommentDeleted;
        $manager->unlock();
      } else {
        $manager->UnsubscribeClass($this);
      }
    }
  }
  
  public function CommentDeleted($id) {
    unset($this->items[$id]);
    $this->save();
  }
  
  public function geturl() {
    global $options;
    return $options->url . '/admin/subscribe/' . $options->q;
  }
  
  public function SendMailToSubscribers($id) {
    if (!$this->SubscribtionEnabled || (isset($this->items[$id]) && $this->items[$id])) return;
    
    $manager = &TCommentManager::instance();
    $item = $manager->GetItem($id);
    if (isset($item['status']) || isset($item['type']))return;
    
    $cron = &TCron::instance();
    $cron->Add('single', get_class($this),  'CronSendMailToSubscribers', $id);
  }
  
  public function CronSendMailToSubscribers($id) {
    global $options;
    $manager = &TCommentManager::instance();
    if (!isset($manager->items[$id])) return;
    $item = $manager->GetItem($id);
    
    $this->items[$id] = true;
    $this->Save();
    
    $comments = &TComments::instance($item['pid']);
    $subscribers = &$comments->GetSubscribers();
    if (in_array($item['uid'], $subscribers)) {
      array_splice($subscribers,  array_search($item['uid'], $subscribers), 1);
    }
    
    if (count($subscribers) == 0) return;
    $comment = new TComment($comments);
    $comment->id = $id;
    
    $html = &THtmlResource::instance();
    $html->section = 'moderator';
    $lang = &TLocal::instance();
    
    eval('$subj = "'. $html->subject . '";');
    eval('$body = "' . $html->subscriberbody . '";');
    
    $url = $this->Geturl();
    
    $users = &TCommentUsers::instance();
    foreach ($subscribers as $userid) {
      $user = $users->GetItem($userid);
      if (empty($user['email'])) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
  $link = "\n{$url}userid={$user['cookie']}\n";
      TMailer::SendMail($options->name, $this->fromemail,  $user['name'], $user['email'],
      $subj, $body . $link);
    }
  }
  
}//class

?>