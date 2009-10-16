<?php

class TComments extends TEventClass {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'comments';
    $this->addevents('dited', 'changed', 'approved');
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
    $template = template::instance();
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
$this->db->delete("post = postid");
$users->db->delete("id in
(select $userstable.id as uid, from $userstable
right join $commentstable on  $commentstable.author = $userstable.id
where uid is  null)");
  }
  
  public function add($postid, $name, $email, $url, $content) {
    $users = TCommentUsers ::instance();
    $author = $users->add($name, $email, $url);
$result =$this->db->InsertAssoc(array(
'post' => $postid,
'parent' => 0,
'author' => $author,
'created' => sqlnow(),
'modified' => sqldate(),
'content' =>
'status' => $this->CreateStatus($author, $content),
'pingback' => 'false'
));
$this->DoAdded($result);
return $result;
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
    $this->added($id);
  }
  
  public function hasauthor($author) {
$this->db->select("author = $author limit 1")
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
  
  public function delete($id) {
$author = $this->db->getvalue($id, 'author');
$this->db->iddelete($id);

           if (!$this->hasauthor($author)) {
        $users = TCommentUsers::instance();
        $users->iddelete($author);
      }
      
      $this->deleted($id);
      $this->DoChanged($postid);
  }
  
  public function DoChanged($postid) {
    TTemplate::WidgetExpired($this);
    
    $post = TPost::instance($postid);
    $Urlmap = TUrlmap::instance();
    $Urlmap->SetExpired($post->url);
    
    $this->Changed($postid);
  }
  
  public function settatus($id, $value) {
    if (!in_array($value, array('approved', 'hold', 'spam')))  return false;
$this->db->setvalue($id, 'status', $value);
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
  
  public function getholditems() {
return $this->db->res2array($this->db->select("status = 'hold' and pingback = false"));
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