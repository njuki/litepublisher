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
$this->db->add(array(
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
  }
  
  public static function PrintForm($postid) {
    global $options;
    $result = '';
    $self = self::instance();
$args = targs::instance();
    $args->name = '';
    $args->email = '';
    $args->url = '';
   $args->subscribe = true;
    $args->content = '';
    $args->postid = $postid;
    $args->antispam = '_Value' . strtotime ("+1 hour");

    if (!empty($_COOKIE["userid"])) {
      $comusers = tcomusers::instance();
      if ($user = $users->GetItemFromCookie($_COOKIE['userid'])) {
        $args->name = $user['name'];
        $args->email = $user['email'];
        $args->url = $user['url'];
$subscribers = tsubscribers::instance();
        $args->subscribe = $subscribers->subscribed($postid, $user['id']);
        
        //hold comment list
        $comments = tcomments::instance($postid);
        $items = $comments->gethold($user['id']);
        if (count($items) > 0) {
          $tc = ttemplatecomments::instance();
          $result .= $tc->gethold($items, $postid);
        }
      }
    }

    $lang = TLocal::instance('comment');

$theme = ttheme::instance();
$result .= $theme->parsearg($theme->comments['form'], $args);
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
      return tsimplecontent::html($this->getconfirmform($confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!($values = $hold->getitem($confirmid))) {
      return tsimplecontent::content(TLocal::$data['commentform']['notfound']);
    }
    
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = $classes->posts;
    if(!$posts->ItemExists($postid)) return tsimplecontent::content(TLocal::$data['default']['postnotfound']);
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
    if (!$this->CheckSpam($values['antispam']))   return tsimplecontent::content($lang->spamdetected);
    if (empty($values['content'])) return tsimplecontent::content($lang->emptycontent);
    if (empty($values['name'])) return tsimplecontent::content($lang->emptyname);
    if (!TContentFilter::ValidateEmail($values['email'])) return tsimplecontent::content($lang->invalidemail);
    if (!$post->commentsenabled) return tsimplecontent::content($lang->commentsdisabled);
    if ($post->status != 'published')  return tsimplecontent::content($lang->commentondraft);
    //check duplicates
    $comments = tcomments::instance($post->id);
    if ($comments->IndexOfRawContent($values['content']) >= 0) return tsimplecontent::content($lang->duplicate);
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
    $users = tcomusers::instance();
    $uid = $users->add($values['name'], $values['email'], $values['url']);
    $usercookie = $users->getcookie($uid);
    if (!$classes->spamfilter->UserCanAdd( $uid)) return tsimplecontent::content($lang->toomany);

$subscribers = tsubscribers::instance();
    $subscribers->update($post->id, $uid, $values['subscribe']);

    $classes->commentmanager->addcomment($post->id, $uid, $values['content']);
    
    return "<?php
    @setcookie('userid', '$usercookie', time() + 30000000,  '/', false);
    @header('Location: $options->url$posturl');
    ?>";
  }
  
  private function getconfirmform($confirmid) {
global $lang;
    $lang = tlocal::instance($this->basename);
$args = targs::instance();
$args->confirmid = $confirmid;
$theme = ttheme::instance();
    return $theme->parsearg($theme->comments['confirmform'], $args);
  }
  
}//class

?>