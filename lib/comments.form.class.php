<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

if (dbversion) {
class tkeptcomments extends tdata {
  
  protected function create() {
    parent::create();
    $this->table ='commentskept';
$this->db->delete("posted < now() - INTERVAL 20 minutes ");
  }

public function add($values) {
      $confirmid = md5(mt_rand() . secret. uniqid( microtime()));
$this->db->add(array(
'id' => $confirmid, 
'posted' => sqldate(),
'vals' => serialize($values)
));
return $confirmid;
}  

public function getitem($confirmid) {
if ($item = $this->getitem($confirmid)) {
return unserialize($item['vals']);
}
return false;
}

}//class

} else {

class tkeptcomments extends titems {
  
  protected function create() {
    parent::create();
    $this->basename ='comments.kept';
  }

public function afterload() {
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

class tcommentform extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->cache = false;
  }
  
  public static function printform($postid) {
    global $options;
    $result = '';
    $self = self::instance();
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$args = targs::instance();
    $args->name = '';
    $args->email = '';
    $args->url = '';
   $args->subscribe = true;
    $args->content = '';
    $args->postid = $postid;
    $args->antispam = '_Value' . strtotime ("+1 hour");

    if (!empty($_COOKIE["userid"])) {
      $comusers = tcomusers::instance($postid);
      if ($user = $comusers->fromcookie($_COOKIE['userid'])) {
        $args->name = $user['name'];
        $args->email = $user['email'];
        $args->url = $user['url'];
$subscribers = tsubscribers::instance();
        $args->subscribe = $subscribers->subscribed($postid, $user['id']);

$comments = tcomments::instance($postid);
$hold = $comments->getholdcontent($user['id']);
if ($hold != '') {
$result .= $theme->parse($theme->content->post->templatecomments->comments->hold);
          $result .= $hold;
}
      }
    }

$result .= $theme->parsearg($theme->content->post->templatecomments->form, $args);
return $result;
 }
  
  private function checkspam($s) {
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
    
    $kept = new tkeptcomments();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $confirmid  = $kept->add($values);
      return tsimplecontent::html($this->getconfirmform($confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!($values = $kept->getitem($confirmid))) {
      return tsimplecontent::content(tlocal::$data['commentform']['notfound']);
    }
    
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = $classes->posts;
    if(!$posts->ItemExists($postid)) return tsimplecontent::content(tlocal::$data['default']['postnotfound']);
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
    if (!$this->checkspam($values['antispam']))   return tsimplecontent::content($lang->spamdetected);
    if (empty($values['content'])) return tsimplecontent::content($lang->emptycontent);
    if (empty($values['name'])) return tsimplecontent::content($lang->emptyname);
    if (!tcontentfilter::ValidateEmail($values['email'])) return tsimplecontent::content($lang->invalidemail);
    if (!$post->commentsenabled) return tsimplecontent::content($lang->commentsdisabled);
    if ($post->status != 'published')  return tsimplecontent::content($lang->commentondraft);
    //check duplicates
    if ($classes->spamfilter->checkduplicate($postid, $values['content']) ) return tsimplecontent::content($lang->duplicate);
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
    $users = tcomusers::instance($postid);
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
    return $theme->parsearg($theme->content->post->templatecomments->confirmform, $args);
  }
  
}//class

?>