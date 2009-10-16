<?php

class TCommentManager extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->AddEvents('Edited', 'Changed', 'Approved');
  }
  
  public function getcomment($id) {
    return tcomments::getcomment($this->items[$id]['pid'], $id);
  }
  
 public function GetWidgetContent($id) {
    global $options;
    $template = ttemplate::instance();
    $result = '';
    $templ = isset($template->theme['widget']['recentcomment']) ? $template->theme['widget']['recentcomment'] :
    '<li><strong><a href="%1$s#comment-%2$s" title="%6$s %3$s">%4$s</a></strong>: %5$s...</li>';
    
    $count = $this->options->recentcount;
    if ($item = end($this->items)) {
      $users = TCommentUsers::instance();
      $onrecent = TLocal::$data['comment']['onrecent'];
      do {
        $id = key($this->items);
        if (!isset($item['status']) && !isset($item['type']) ) {
          $count--;
          $post = tpost::instance($item['pid']);
          $content = $post->comments->getvalue($id, 'content');
          $content = TContentFilter::GetExcerpt($content, 120);
          $user = $users->getitem($item['uid']);
          $result .= sprintf($templ, $options->url . $post->url, $id,$post->title, $user['name'], $content, $onrecent);
        }
      } while (($count > 0) && ($item  = prev($this->items)));
    }
    
    return $result;
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
    $post = tpost::instance($postid);
    return $this->AddToPost($post, $userid, $content);
  }
  
  public function AddToPost(&$post, $userid, $content) {
    $id = ++  $this->lastid;
    $comments = &$post->comments;
    $status = $this->CreateStatus($userid, $content);
    $date = $comments->Create($id, $userid,  $content, $status);
    
    $this->items[$id] = array(
    //'id' => $id,
    'uid' => (int) $userid,
    'pid' => (int) $post->id,
    'date' => $date
    );
    if ($status != 'approved') $this->items[$id]['status'] = $status;
    $this->save();
    $this->DoAdded($id);
  }
  
  protected function CreateStatus($userid, $content) {
    global $options;
    if ($options->DefaultCommentStatus == 'approved') return 'approved';
    if ($this->UserHasApproved($userid)) return  'approved';
    return 'hold';
  }
  
  public function AddPingback(&$post, $url, $title) {
    $id =++$this->lastid;
    $users = &TCommentUsers::instance();
    $userid = $users->Add($title, '', $url);
    $comments = &$post->comments;
    $date = $comments->Create($id, $userid, '', 'hold', 'pingback');
    
    $this->items[$id] = array(
    //'id' => $id,
    'uid' => $userid,
    'pid' => (int) $post->id,
    'date' => $date,
    'status' => 'hold',
    'type' => 'pingback'
    );
    $this->save();
    $this->DoAdded($id);
  }
  
  private function DoAdded($id) {
    $this->DoChanged($this->items[$id]['pid']);
    $this->CommentAdded($id);
    $this->Added($id);
  }
  
  public function hasauthor($author) {
    foreach ($this->items as $id => $item) {
      if ($author == $item['uid'])  return true;
    }
    return false;
  }
  
  public function UserHasApproved($userid) {
    foreach ($this->items as $id => $item) {
      if (($userid == $item['uid']) && !isset($item['status'])) return true;
    }
    return false;
  }
  
  public function HasApprovedCount($userid, $count) {
    foreach ($this->items as $id => $item) {
      if (($userid == $item['uid']) && !isset($item['status'])) {
        if (--$count ==0) return true;
      }
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
  
  public function DoChanged($postid) {
    TTemplate::WidgetExpired($this);
    
    $post = TPost::instance($postid);
    $Urlmap = TUrlmap::instance();
    $Urlmap->SetExpired($post->url);
    
    $this->Changed($postid);
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
  
  public function UserCanAdd($userid) {
    $count = 0;
    $approved = 0;
    foreach($this->items as $id => $item) {
      if ($item['uid'] == $userid) {
        $count++;
        if (!isset($item['status']) ) $approved++;
      }
    }
    if ($count < 2) return true;
    if  ($approved ==0) return false;
    return true;
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
  
  public function CommentAdded($id) {
    global $options;
    if (!$this->SendNotification) return;
    $comment = &$this->Getcomment($id);
    $html = &THtmlResource::instance();
    $html->section = 'moderator';
    $lang = &TLocal::instance();
    eval('$subject = "' . $html->subject . '";');
    eval('$body = "'. $html->body . '";');
    TMailer::SendMail($options->name, $options->fromemail,
    'admin', $options->email,  $subject, $body);
  }
  
}//class

?>