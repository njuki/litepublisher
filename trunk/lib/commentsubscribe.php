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

public function add($pid, $uid) {
if (dbversion) {
$this->db->InsertAssoc(array(
'post' => $pid,
'author' => $uid
));
} else {
if (!isset($this->items[$pid]))  $this->items[$pid] = array();
if (!in_array($uid, $this->items[$pid])) {
$this->items[$pid][] =$uid;
$this->save();
return true;
}
return false;
}
}

public function delete($pid, $uid) {
if (dbversion) {
return $this->db->delete("post = $pid and $author = $uid");
} elseif (isset($this->items[$pid])) {
    $i = array_search($uid, $this->items[$pid]);
    if (is_int($i))  {
array_splice($this->items[$pid], $i, 1);
$this->save();
return true;
}
return false;
}
}

public function deletepost($pid) {
if (dbversion) {
$this->db->delete("post = $pid");
} elseif (isset($this->items[$pid])) {
unset($this->items[$pid]);
$this->save();
} else {
}

public function deleteauthor($uid) {
if (dbversion) {
$this->db->delete("author = $uid");
} else {
foreach ($this->items as $pid => $item) {
    $i = array_search($uid, $item);
    if (is_int($i))  array_splice($this->items[$pid], $i, 1);
    }
$this->save();
}
}

  public function update($pid, $uid, $subscribed) {
if (dbversion) {
$this->delete($pid, $uid);
if ($subscribed) $this->add($pid, $uid);
} elseif ($subscribed) {
if (!isset($this->items[$pid]))  $this->items[$pid] = array();
if (!in_array($uid, $this->items[$pid])) {
$this->items[$pid][] =$uid;
$this->save();
}
} else {
$this->delete($pid, $uid);
}

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