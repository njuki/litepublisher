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
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->data['locklist'] = '';
  }
  
  public function load() {
    $filename = litepublisher::$paths->data . $this->getbasename() .'.php';
    if (@file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
tfilestorage::save($this);
  }
  
  public function update($pid, $uid, $subscribed) {
    if ($subscribed == $this->subscribed($pid, $uid)) return;
    if (dbversion) {
      $this->remove($pid, $uid);
      if ($subscribed) $this->add($pid, $uid);
    } elseif ($subscribed) {
      $this->items[$pid][] =$uid;
      $this->save();
    } else {
      $this->remove($pid, $uid);
    }
  }
  
  public function subscribed($pid, $uid) {
    if (dbversion) {
      return $this->db->exists("post = $pid and item = $uid");
    } else {
      return isset($this->items[$pid]) && in_array($uid, $this->items[$pid]);
    }
  }
  
  public function setenabled($value) {
    if ($this->enabled != $value) {
      $this->data['enabled'] = $value;
      $this->save();
      $manager = litepublisher::$classes->commentmanager;
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
  
  public function sendmail($id, $idpost) {
    if (!$this->enabled) return;
    $comments = tcomments::instance($idpost);
    if (!$comments->itemexists($id)) return;
    $item = $comments->getitem($id);
    if (dbversion) {
      if (($item['status'] != 'approved')) return;
    }
    
    $cron = tcron::instance();
    $cron->add('single', get_class($this),  'cronsendmail', array((int) $id, (int) $idpost));
  }
  
  public function cronsendmail($arg) {
    $id = $arg[0];
    $pid = $arg[1];
    $comments = tcomments::instance($pid);
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    $subscribers  = $this->getitems($pid);
    if (!$subscribers  || (count($subscribers ) == 0)) return;
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $mailtemplate = tmailtemplate::instance('comments');
    $subject = $mailtemplate->subscribesubj ();
    $body = $mailtemplate->subscribebody();
    $body .= sprintf("\n%s/admin/subscribers/%suserid=", litepublisher::$site->url, litepublisher::$site->q);
    
    $comusers = tcomusers::instance();
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if ($user['email'] == $comment->email) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
      tmailer::sendmail(litepublisher::$options->name, $this->fromemail,  $user['name'], $user['email'],
      $subject, $body . $user['cookie']);
    }
  }
  
}//class

?>