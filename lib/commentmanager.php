<?php

class TCommentManager extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'comments';
    $this->basename = 'commentmanager';
    $this->addevents('edited', 'changed', 'approved');
    $this->data['recentcount'] =  7;
    $this->data['SendNotification'] =  true;
  }
  
  public function getcomment($id) {
    return tcomments::getcomment($this->items[$id]['pid'], $id);
  }
  
  public function SetSendNotification($value) {
    if ($this->SendNotification != $value) {
      $this->data['SendNotification'] = $value;
      $this->save();
    }
  }
  
  public function Setrecentcount($value) {
    if ($value != $this->recentcount) {
      $this->data['recentcount'] = $value;
      $this->save();
    }
  }
  
  public function GetWidgetContent($id) {
    global $options;
    $template = ttemplate::instance();
    $result = '';
    $templ = isset($template->theme['widget']['recentcomment']) ? $template->theme['widget']['recentcomment'] :
    '<li><strong><a href="%1$s#comment-%2$s" title="%6$s %3$s">%4$s</a></strong>: %5$s...</li>';
    
    $count = $this->recentcount;
    if ($item = end($this->items)) {
      $users = TCommentUsers::instance();
      $onrecent = TLocal::$data['comment']['onrecent'];
      do {
        $id = key($this->items);
        if (!isset($item['status']) && !isset($item['type']) ) {
          $count--;
          $post = &TPost::instance($item['pid']);
          $content = $post->comments->GetValue($id, 'content');
          $content = TContentFilter::GetExcerpt($content, 120);
          $user = $users->GetItem($item['uid']);
          $result .= sprintf($templ, $options->url . $post->url, $id,$post->title, $user['name'], $content, $onrecent);
        }
      } while (($count > 0) && ($item  = prev($this->items)));
    }
    
    return $result;
  }
  
  public function PostDeleted($postid) {
if (dbversion) {
return $this->db->delete("post = $postid");
}
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
  
  public function AddToPost($post, $userid, $content) {
    $status = $this->CreateStatus($userid, $content);
$id = $post->comments->add($userid,  $content, $status);
$item = array(
    'uid' => (int) $userid,
    'pid' => (int) $post->id,
    'created' => $date
    );

    if ($status != 'approved') $item['status'] = $status;

if (dbversion) {
} else {
    $this->items[++$this->lastid] = $item;
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
  
  public function HasUser($userid) {
    foreach ($this->items as $id => $item) {
      if ($userid == $item['uid'])  return true;
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
  
  public function Delete($id) {
    if (isset($this->items[$id])) {
      $this->lock();
      $comments = &TComments::instance($this->items[$id]['pid']);
      $comments->Delete($id);
      $postid = $this->items[$id]['pid'];
      $userid = $this->items[$id]['uid'];
      unset($this->items[$id]);
      $this->unlock();
      
      if (!$this->HasUser($userid)) {
        $users = &TCommentUsers::instance();
        $users->Delete($userid);
      }
      
      $this->Deleted($id);
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
  
  public function SetStatus($id, $value) {
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