<?php

class THoldComments extends TItems {
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename ='holdcomments';
  }
  
}

class TCommentForm extends TEventClass{
  
  public static function &Instance() {
    return GetNamedInstance('commentform', __class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename ='commentform';
    $this->CacheEnabled = false;
    $this->Data['form'] = '';
    $this->Data['confirmtemplate'] = '';
  }
  
  public function Setform($form) {
    $this->Data['form'] = str_replace('"', '\"', $form);
    $this->save();
  }
  
  public static function PrintForm($postid) {
global $Options;
    $result = '';
    $self = GetNamedInstance('commentform', __class__);
    $values = array(
    'name' => '',
    'email' => '',
    'url' => '',
    'subscribe' => 'checked',
    'content' => '',
    'postid' => $postid,
    'antispam' => '_Value' . strtotime ("+1 hour")
    );
    
    if (!empty($_COOKIE["userid"])) {
      $users = TCommentUsers::Instance();
      if ($user = $users->GetItemFromCookie($_COOKIE['userid'])) {
        $values['name'] = $user['name'];
        $values['email'] = $user['email'];
        $values['url'] = $user['url'];
        $values['subscribe'] = $users->subscribed($user['id'], $postid) ? 'checked' : '';
        
        //hold comment list
        $comments = TComments::Instance($postid);
        $items = &$comments->GetHold($user['id']);
        if (count($items) > 0) {
          $TemplateComment = TTemplateComment::Instance();
          $result .= $TemplateComment->GetHoldList($items, $postid);
        }
      }
    }
    $lang = TLocal::Instance('comments');
    eval('$result .= "'. $self->form . '\n";');
    return $result;

  }
  
  private function CheckSpam($s) {
    $TimeKey = (int) substr($s, strlen('_Value'));
    return time() < $TimeKey;
  }
  
  public function Request($param) {
    global $Options;
    if ($Options->commentsdisabled) return 404;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed');
      @header('Content-Type: text/plain');
      ?>";
    }
    
    $posturl = $Options->home;
    
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    
    
    $postid = (int) $_POST['postid'];
    $hold = new THoldComments();
    foreach ($hold->items as $id => $item) {
      if ($item['date']+ 600 < time()) unset($hold->items[$id]);
    }
    
    if (!isset($_POST['confirmid'])) {
      $confirmid = md5($postid . secret. uniqid( microtime()));
      $values = $_POST;
      $values['date'] = time();
      $hold->items[$confirmid] =$values;
      $hold->Save();
      return TTemplate::SimpleHtml($this->GetConfirmForm($postid, $confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!isset($hold->items[$confirmid])) {
      return TTemplate::SimpleContent(TLocal::$data['commentform']['notfound']);
    }
    
    $values = $hold->items[$confirmid];
    unset($hold->items[$confirmid]);
    $hold->Save();
    
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = TPosts::Instance();
    if(!$posts->ItemExists($postid)) return TTemplate::SimpleContent(TLocal::$data['default']['postnotfound']);
    $post = &TPost::Instance($postid);
    
    $values = array(
    'name' => isset($values['name']) ? TContentFilter::escape($values['name']) : '',
    'email' => isset($values['email']) ? trim($values['email']) : '',
    'url' => isset($values['url']) ? TContentFilter::escape($values['url']) : '',
    'subscribe' => isset($values['subscribe']),
    'content' => isset($values['content']) ? trim($values['content']) : '',
    'postid' => $postid,
    'antispam' => isset($vlues['antispam']) ? $values['antispam'] : ''
    );
    
    $lang = TLocal::Instance('comment');
    if (!$this->CheckSpam($values['antispam']))   return TTemplate::SimpleContent($lang->spamdetected);
    if (empty($values['content'])) return TTemplate::SimpleContent($lang->emptycontent);
    if (empty($values['name'])) return TTemplate::SimpleContent($lang->emptyname);
    if (!TContentFilter::ValidateEmail($values['email'])) return TTemplate::SimpleContent($lang->invalidemail);
    if (!$post->commentsenabled) return TTemplate::SimpleContent($lang->commentsdisabled);
    if ($post->status != 'published')  return TTemplate::SimpleContent($lang->commentondraft);
    //check duplicates
    $comments = $post->comments;
    if ($comments->IndexOfRawContent($values['content']) >= 0) return TTemplate::SimpleContent($lang->duplicate);
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->pagescount/" : $post->url;
    $users = TCommentUsers ::Instance();
    $users->lock();
    $userid = $users->Add($values['name'], $values['email'], $values['url']);
    $CommentManager = &TCommentManager::Instance();
    if (!$CommentManager->UserCanAdd( $userid)) return TTemplate::SimpleContent($lang->toomany);
    $users->UpdateSubscribtion($userid, $post->id, $values['subscribe']);
    $usercookie = $users->GetCookie($userid);
    $users->unlock();
    
    $CommentManager->AddToPost($post, $userid, $values['content']);
    
    return "<?php
    @setcookie('userid', '$usercookie', time() + 30000000,  '/', false);
    @header('Location: $Options->url$posturl');
    ?>";
  }
  
  private function GetConfirmForm($postid, $confirmid) {
    $lang = TLocal::Instance($this->basename);
    $tml = $this->GetConfirmFormTemplate();
    eval('$result = "'. $tml . '\n";');
    return $result;
  }
  
  private function GetConfirmFormTemplate() {
    global $paths;
    $filename = $this->confirmtemplate == '' ? 'confirmform.tml' : $this->confirmtemplate;
    return file_get_contents($paths['libinclude'] . $filename);
  }
  
  
}//class

?>