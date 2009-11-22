<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsubscribers extends titemsposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->data['locklist'] = '';
  }

  public function update($pid, $uid, $subscribed) {
if ($subscribed == $this->subscribed($pid, $uid)) return;
if (dbversion) {
$this->delete($pid, $uid);
if ($subscribed) $this->add($pid, $uid);
} elseif ($subscribed) {
$this->items[$pid][] =$uid;
$this->save();
} else {
$this->delete($pid, $uid);
}
  }

public function subscribed($pid, $uid) {
if (dbversion) {
return $this->db->exists("post = $pid and author = $uid");
 } else {
return isset($this->items[$pid]) && in_array($uid, $this->items[$pid]);
}
}
  
   public function setenabled($value) {
global $classes;
    if ($this->enabled != $value) {
      $this->data['enabled'] = $value;
      $this->save();
      $manager = $classes->commentmanager;
      if ($value) {
        $manager->lock();
        $manager->added = $this->sendmail;
        $manager->approved = $this->sendmail;
        $manager->unlock();
      } else {
        $manager->unsubscribeclass($this);
      }
    }
  }

  public function sendmail($id) {
global $classes;
    if (!$this->enabled) return;
    
    $manager = $classes->commentmanager;
    $item = $manager->getitem($id);
if (dbversion) {
if (($item['status'] != 'approved') || ($item['pingback'] == '1')) return;
} else {
    if (isset($item['status']) || isset($item['type']))return;
}
    
    $cron = tcron::instance();
    $cron->add('single', get_class($this),  'cronsendmail', $id);
  }
  
  public function cronsendmail($id) {
    global $options, $classes, $comment;
    $manager = $classes->commentmanager;
    if (!$manager->itemexists($id)) return;
    $item = $manager->getitem($id);
$pid = $item['pid'];
if (dbversion) {
if ($this->db->getcount("post = $pid") == 0) return;
} else {
if (!isset($this->items[$pid]) || (count($this->items[$pid]) == 0)) return;
}
   
    $comment = $manager->getcomment($id);

    $html = THtmlResource::instance();
    $html->section = 'comments';
    
$subj = $html->subject();
$body = $html->subscriberbody();
    $body .= "\n$options->url/admin/subscribers/{$options->q}userid=";
    
    $users = tcomusers::instance();
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
      tmailer::sendmail($options->name, $this->fromemail,  $user['name'], $user['email'],
      $subj, $body . $user['cookie']);
    }
  }
  
}//class

?>