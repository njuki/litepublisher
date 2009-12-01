<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcommentmanager extends TAbstractCommentManager implements icommentmanager {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function addcomment($pid, $uid, $content) {
global $classes;
    $id = ++  $this->autoid;
    $comments = tcomments::instance($pid);
    $status = $classes->spamfilter->createstatus($uid, $content);
$comments->insert($id, $uid,  $content, $status, '');
    
    $this->items[$id] = array(
    'uid' => (int) $uid,
    'pid' => (int) $pid,
    'posted' => $comments->items[$id]['posted'],
    );
    if ($status != 'approved') $this->items[$id]['status'] = $status;
    $this->save();
    $this->doadded($id, $pid);
  }
  
 public function addpingback($pid, $url, $title) {
    $id =++$this->autoid;
    $comusers = tcomusers::instance();
    $uid = $comusers->add($title, '', $url);
    $comments = tcomments::instance($pid);
$comments->insert($id, $uid, '', 'hold', 'pingback');
    
    $this->items[$id] = array(
    'uid' => $uid,
'parent' => 0,
    'pid' => (int) $pid,
    'posted' => time(),
    'status' => 'hold',
    'type' => 'pingback'
    );
    $this->save();
    $this->doadded($id, $pid);
  }

  public function getcomment($id) {
    return tcomments::getcomment($this->items[$id]['pid'], $id);
  }
 
 private function hasauthor($author) {
    foreach ($this->items as $id => $item) {
      if ($author == $item['uid'])  return true;
    }
    return false;
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      $this->lock();
      $comments = tcomments::instance($this->items[$id]['pid']);
      $comments->Delete($id);
      $pid = $this->items[$id]['pid'];
      $uid = $this->items[$id]['uid'];
      unset($this->items[$id]);
      $this->unlock();
      
      if (!$this->hasauthor($uid)) {
        $comusers = tcomusers::instance();
        $comusers->delete($uid);
      }
      
      $this->deleted($id);
      $this->dochanged($pid);
      return true;
    }
    return false;
  }

  public function postdeleted($pid) {
    $this->lock();
    foreach ($this->items as  $id => $item) {
      if ($item['pid'] == $pid) {
        unset($this->items[$id]);
      }
    }
    $this->unlock();

$users = array();
foreach ($this->items as $id => $item) {
$users[$item['uid']] = 1;
}

$comusers = tcomusers::instance();
foreach ($comusers->items as $uid => $user) {
if (!isset($users[$uid])) unset($comusers->items[$uid]);
}
$comusers->save();
  }
  
    public function setstatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
    $item = $this->items[$id];
    if ( (($value == 'approved') && !isset($item['status']))  || ($value == $item['status'])) return false;
    
    $comments = tcomments::instance($item['pid']);
    $comments->SetStatus($id, $value);
    
    $this->lock();
    if ($status == 'approved') {
      unset($this->items[$id]['status']);
      if (!isset($item['type'])) $this->approved($id);
    } else {
      $this->items[$id]['status'] = $value;
    }
    $this->unlock();
    $this->dochanged($item['pid']);
  }

public function hasapproved($uid, $count) {
foreach ($this->items as $id => $item) {
if (($uid == $item['uid']) && !isset(4item['status'])) {
if (--$count == 0) return true;
}
}
return false;
}
  
}//class

?>