<?php

class TCommentManager extends TAbstractCommentManager {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function addcomment($postid, $userid, $content) {
global $classes;
    $id = ++  $this->lastid;
    $comments = tcomments::instance($postid);
    $status = $classes->spamfilter->createstatus($userid, $content);
    $posted = $comments->add($id, $userid,  $content, $status);
    
    $this->items[$id] = array(
    //'id' => $id,
    'uid' => (int) $userid,
    'pid' => (int) $post->id,
    'posted' => $posted
    );
    if ($status != 'approved') $this->items[$id]['status'] = $status;
    $this->save();
    $this->DoAdded($id);
  }
  
 public function addpingback($post, $url, $title) {
    $id =++$this->lastid;
    $users = TCommentUsers::instance();
    $userid = $users->add($title, '', $url);
    $comments = tcomments::instance($post->id);
$comments->insert($id, $userid, '', 'hold', 'pingback');
    
    $this->items[$id] = array(
    'uid' => $userid,
    'pid' => (int) $post->id,
    'posted' => time(),
    'status' => 'hold',
    'type' => 'pingback'
    );
    $this->save();
    $this->DoAdded($id);
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
        $users = TCommentUsers::instance();
        $users->delete($uid);
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
  
  public function Getholditems() {
    $result = array();
    foreach($this->items as $id => $item) {
      if (!empty($item['status']) && ($item['status'] == 'hold')) {
        $result[$id] = $item;
      }
    }
    return $result;
  }
  
}//class

?>