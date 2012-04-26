<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsubscribers extends titemsposts {
  public $blacklist;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->addmap('blacklist', array());
  }
  
  public function load() {
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    tfilestorage::save($this);
  }
  
  public function update($pid, $uid, $subscribed) {
    if ($subscribed == $this->exists($pid, $uid)) return;
    if (dbversion) {
      $this->remove($pid, $uid);
      $user = tcomusers::i()->getitem($uid);
      if (in_array($user['email'], $this->blacklist)) return;
      if ($subscribed) $this->add($pid, $uid);
    } else {
      if ($subscribed) {
        $user = tcomusers::i($pid)->getitem($uid);
        $subscribed = !in_array($user['email'], $this->blacklist);
      }
      if ($subscribed) {
        $this->add($pid, $uid);
      } else {
        $this->remove($pid, $uid);
      }
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
        $manager->unbind($this);
      }
    }
  }
  
  public function getlocklist() {
    return implode("\n", $this->blacklist);
  }
  
  public function setlocklist($s) {
    $this->setblacklist(explode("\n", strtolower(trim($s))));
  }
  
  public function setblacklist(array $a) {
    $a = array_unique($a);
    array_delete_value($a, '');
    $this->data['blacklist'] = $a;
    $this->save();
    
    if (dbversion) {
      $dblist = array();
      foreach ($a as $s) {
        if ($s == '') continue;
        $dblist[] = dbquote($s);
      }
      if (count($dblist) > 0) {
        $db = $this->db;
        $db->delete("item in (select id from $db->comusers where email in (" . implode(',', $dblist) . '))');
      }
    }
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->enabled) return;
    $comments = tcomments::i($idpost);
    if (!$comments->itemexists($id)) return;
    $item = $comments->getitem($id);
    if (dbversion) {
      if (($item['status'] != 'approved')) return;
    }
    
    tcron::i()->add('single', get_class($this),  'cronsendmail', (int) $id);
  }
  
  public function cronsendmail($id) {
    $comments = tcomments::i($pid);
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    $subscribers  = $this->getitems($item['post']);
    if (!$subscribers  || (count($subscribers ) == 0)) return;
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $mailtemplate = tmailtemplate::i('comments');
    $subject = $mailtemplate->subscribesubj ();
    $body = $mailtemplate->subscribebody();
    $body .= sprintf("\n%s/admin/subscribers/%suserid=", litepublisher::$site->url, litepublisher::$site->q);
    
    $comusers = tcomusers::i();
    $comusers->loaditems($subscribers);
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if ($user['email'] == $comment->email) continue;
      if (in_array($user['email'], $this->blacklist)) continue;
      tmailer::sendmail(litepublisher::$site->name, $this->fromemail,  $user['name'], $user['email'],
      $subject, $body . rawurlencode($user['cookie']));
    }
  }
  
}//class

?>