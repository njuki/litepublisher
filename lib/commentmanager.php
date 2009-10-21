<?php

class TCommentManager extends TAbstractCommentManager {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcomment($id) {
    return tcomments::getcomment($this->items[$id]['pid'], $id);
  }
  
  public function PostDeleted($postid) {
    $this->lock();
    foreach ($this->items as  $id => $item) {
      if ($item['pid'] == $postid) {
        unset($this->items[$id]);
      }
    }
    $this->unlock();
  }
  
  public function add($postid, $name, $email, $url, $content) {
    $users = TCommentUsers ::instance();
    $userid = $users->add($name, $email, $url);
    return $this->AddToPost($postid, $userid, $content);
  }
  
  public function addcomment($postid, $userid, $content) {
    $id = ++  $this->lastid;
    $comments = tcomments::instance($postid);
    $status = $this->CreateStatus($userid, $content);
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
  
 public function addpingback(&$post, $url, $title) {
    $id =++$this->lastid;
    $users = &TCommentUsers::instance();
    $userid = $users->Add($title, '', $url);
    $comments = &$post->comments;
    $posted = $comments->add($id, $userid, '', 'hold', 'pingback');
    
    $this->items[$id] = array(
    //'id' => $id,
    'uid' => $userid,
    'pid' => (int) $post->id,
    'posted' => $posted,
    'status' => 'hold',
    'type' => 'pingback'
    );
    $this->save();
    $this->DoAdded($id);
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
      $comments = &TComments::instance($this->items[$id]['pid']);
      $comments->Delete($id);
      $postid = $this->items[$id]['pid'];
      $userid = $this->items[$id]['uid'];
      unset($this->items[$id]);
      $this->unlock();
      
      if (!$this->hasauthor($userid)) {
        $users = TCommentUsers::instance();
        $users->delete($userid);
      }
      
      $this->deleted($id);
      $this->DoChanged($postid);
      return true;
    }
    return false;
  }
  
  public function setstatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
    $item = $this->items[$id];
    if ( (($value == 'approved') && !isset($item['status']))  || ($value == $item['status'])) return false;
    
    $comments = &TComments::instance($item['pid']);
    $comments->SetStatus($id, $value);
    
    $this->lock();
    if ($status == 'approved') {
      unset($this->items[$id]['status']);
      if (!isset($item['type'])) $this->Approved($id);
    } else {
      $this->items[$id]['status'] = $value;
    }
    $this->unlock();
    $this->DoChanged($item['pid']);
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