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
    $this->dbversion = dbversion;
    $this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->data['locklist'] = '';
  }
  
  public function load() {
    global $paths;
    $filename = $paths['data'] . $this->getbasename() .'.php';
    if (@file_exists($filename)) {
      return $this->LoadFromString(PHPUncomment(file_get_contents($filename)));
    }
  }
  
  public function save() {
    global $paths;
    if (self::$GlobalLock || $this->locked) return;
    SafeSaveFile($paths['data'].$this->getbasename(), PHPComment($this->SaveToString()));
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
      return $this->db->exists("post = $pid and item = $uid");
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
  
  public function sendmail($id, $idpost) {
    if (!$this->enabled) return;
    $comments = tcomments::instance($idpost);
    $item = $comments->getitem($id);
    if (dbversion) {
      if (($item['status'] != 'approved')) return;
    }
    
    $cron = tcron::instance();
    $cron->add('single', get_class($this),  'cronsendmail', array($id, $idpost));
  }
  
  public function cronsendmail($arg) {
    global $options, $classes, $comment;
    $id = $arg[0];
    $pid = $arg[1];
    $comments = tcomments($pid);
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    if (dbversion) {
      if ($this->db->getcount("post = $pid") == 0) return;
    } else {
      if (!isset($this->items[$pid]) || (count($this->items[$pid]) == 0)) return;
    }
    
    $comment = $comments->getcomment($id);
$mailtemplate = tmailtemplate::instance('comments');
    $subject = $mailtemplate->subscribesubj ();
    $body = $mailtemplate->subscribebody();
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