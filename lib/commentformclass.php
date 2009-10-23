
<?php

if (dbversion) {
class THoldComments extends TDataclass {
  
  protected function create() {
    parent::create();
    $this->table ='holdcomments';
$this->db->delete("posted + INTERVAL 20 minutes < now");
  }

public function add($values) {
      $confirmid = md5(mt_rand() . secret. uniqid( microtime()));
$this->db->InsertAssoc(array(
'id' => $confirmid, 
'posted' => sqldate(),
'values' => serialize($values)
));
return $confirmid;
}  

public function getitem($confirmid) {
if ($item = $this->getitem($confirmid)) {
return unserialize($item['values']);
}
return false;
}

}//class

} else {

class THoldComments extends TItems {
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename ='holdcomments';
  }

protected function Afterload() {
parent::AfterLoad();
    foreach ($this->items as $id => $item) {
      if ($item['date']+ 600 < time()) unset($this->items[$id]);
    }
}

public function add($values) {
      $confirmid = md5(mt_rand() . secret. uniqid( microtime()));
      $this->items[$confirmid] =$values;
$this->save();
return $confirmid;
}  

public function getitem($confirmid) {
if (!isset($this->items[$confirmid])) return false;
$this->save();
return $this->items[$confirmid];
}

}//class

}

class TCommentForm extends TEventClass{
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->CacheEnabled = false;
    $this->data['form'] = '';
    $this->data['confirmtemplate'] = '';
  }
  
  public function Setform($form) {
    $this->data['form'] = str_replace('"', '\"', $form);
    $this->save();
  }
  
  public static function PrintForm($postid) {
    global $options;
    $result = '';
    $self = self::instance();
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
      $users = TCommentUsers::instance();
      if ($user = $users->GetItemFromCookie($_COOKIE['userid'])) {
        $values['name'] = $user['name'];
        $values['email'] = $user['email'];
        $values['url'] = $user['url'];
        $values['subscribe'] = $users->subscribed($user['id'], $postid) ? 'checked' : '';
        
        //hold comment list
        $comments = tcomments::instance($postid);
        $items = $comments->gethold($user['id']);
        if (count($items) > 0) {
          $TemplateComment = TTemplateComment::instance();
          $result .= $TemplateComment->GetHoldList($items, $postid);
        }
      }
    }
    $lang = TLocal::instance('comment');
    eval('$result .= "'. $self->form . '\n";');
    return $result;
    
  }
  
  private function CheckSpam($s) {
    $TimeKey = (int) substr($s, strlen('_Value'));
    return time() < $TimeKey;
  }
  
  public function request($arg) {
    global $classes, $options;
    if ($options->commentsdisabled) return 404;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed');
      @header('Content-Type: text/plain');
      ?>";
    }
    
    $posturl = $options->home;
    
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    
    $hold = new THoldComments();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $confirmid  = $hold->add($values);
      return ttemplate::SimpleHtml($this->GetConfirmForm($confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!($values = $hold->getitem($confirmid))) {
      return ttemplate::SimpleContent(TLocal::$data['commentform']['notfound']);
    }
    
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = $classes->posts;
    if(!$posts->ItemExists($postid)) return ttemplate::SimpleContent(TLocal::$data['default']['postnotfound']);
    $post = tpost::instance($postid);
    
    $values = array(
    'name' => isset($values['name']) ? TContentFilter::escape($values['name']) : '',
    'email' => isset($values['email']) ? trim($values['email']) : '',
    'url' => isset($values['url']) ? TContentFilter::escape($values['url']) : '',
    'subscribe' => isset($values['subscribe']),
    'content' => isset($values['content']) ? trim($values['content']) : '',
    'postid' => $postid,
    'antispam' => isset($values['antispam']) ? $values['antispam'] : ''
    );
    
    $lang = tlocal::instance('comment');
    if (!$this->CheckSpam($values['antispam']))   return ttemplate::SimpleContent($lang->spamdetected);
    if (empty($values['content'])) return ttemplate::SimpleContent($lang->emptycontent);
    if (empty($values['name'])) return ttemplate::SimpleContent($lang->emptyname);
    if (!TContentFilter::ValidateEmail($values['email'])) return ttemplate::SimpleContent($lang->invalidemail);
    if (!$post->commentsenabled) return ttemplate::SimpleContent($lang->commentsdisabled);
    if ($post->status != 'published')  return ttemplate::SimpleContent($lang->commentondraft);
    //check duplicates
    $comments = tcomments::instance($post->id);
    if ($comments->IndexOfRawContent($values['content']) >= 0) return ttemplate::SimpleContent($lang->duplicate);
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commenspages/" : $post->url;
    $users = TCommentUsers ::instance();
    $users->lock();
    $uid = $users->add($values['name'], $values['email'], $values['url']);
    if (!$classes->spamfilter->UserCanAdd( $uid)) return ttemplate::SimpleContent($lang->toomany);

$subscribers = tsubscribers::instance();
    $subscribers->update($post->id, $uid, $values['subscribe']);

    $usercookie = $users->getcookie($uid);
    $users->unlock();
    
    $classes->commentmanager->addcomment($post->id, $uid, $values['content']);
    
    return "<?php
    @setcookie('userid', '$usercookie', time() + 30000000,  '/', false);
    @header('Location: $options->url$posturl');
    ?>";
  }
  
  private function GetConfirmForm($confirmid) {
    $lang = TLocal::instance($this->basename);
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