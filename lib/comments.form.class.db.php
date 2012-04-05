<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
if (!class_exists('tkeptcomments', false)) {
    class tkeptcomments extends tdata {
      
      public static function i() {
        return getinstance(__class__);
      }
      
      protected function create() {
        parent::create();
        $this->table ='commentskept';
        
      }
      
      public function deleteold() {
        $this->db->delete(sprintf("posted < '%s' - INTERVAL 20 minute ", sqldate()));
      }
      
      public function add($values) {
        $confirmid = md5uniq();
        $this->db->add(array(
        'id' => $confirmid,
        'posted' => sqldate(),
        'vals' => serialize($values)
        ));
        return $confirmid;
      }
      
      public function getitem($confirmid) {
        if ($item = $this->db->getitem(dbquote($confirmid))) {
          return unserialize($item['vals']);
        }
        return false;
      }
      
    }//class
    
}

class tcommentform extends tevents {
  public $htmlhelper;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->cache = false;
    $this->htmlhelper = $this;
  }
  
  public static function getcomuser($postid) {
    if (!empty($_COOKIE['userid'])) {
      $cookie = basemd5($_COOKIE['userid']  . litepublisher::$secret);
      $comusers = tcomusers::i($postid);
      $user = $comusers->fromcookie($cookie);
      $comusers->loadall();
      if (!dbversion && !$user && !empty($_COOKIE["idpost"])) {
        $comusers2 = tcomusers::i( (int) $_COOKIE['idpost']);
        $user = $comusers2->fromcookie($cookie);
      }
      return $user;
    }
    return false;
  }
  
  public function getform(tpost $post, ttheme $theme) {
    $result = '';
    $lang = tlocal::i('comment');
    $args = new targs();

switch ($post->comments_status) {
case 'reg':
break;

case 'guest':
break;

case 'notconfirm':

    $args->name = '';
    $args->email = '';
    $args->url = '';
    $args->subscribe = litepublisher::$options->defaultsubscribe;
    $args->content = '';
    $args->postid = $postid;
    $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
    
    if ($user = self::getcomuser($postid)) {
      $args->name = $user['name'];
      $args->email = $user['email'];
      $args->url = $user['url'];
      $subscribers = tsubscribers::i();
      $args->subscribe = $subscribers->exists($postid, $user['id']);
      
      $comments = tcomments::i($postid);
      if ($hold = $comments->getholdcontent($user['id'])) {
        $result .= $hold;
      }
    }
    
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
    return $result;
  }
  
  private function checkspam($s) {
    if  (!($s = @base64_decode($s))) return false;
    $sign = 'superspamer';
    if (!strbegin($s, $sign)) return false;
    $TimeKey = (int) substr($s, strlen($sign));
    return time() < $TimeKey;
  }
  
  public function request($arg) {
    if (litepublisher::$options->commentsdisabled) return 404;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Content-Type: text/plain');
      ?>";
    }
    
    $posturl = litepublisher::$site->url . '/';
    tguard::post();
    
    $kept = tkeptcomments::i();
    $kept->deleteold();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
      $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
      $posts = litepublisher::$classes->posts;
      if(!$posts->itemexists($postid)) {
        return $this->htmlhelper->geterrorcontent($lang->postnotfound);
      }
      
      $post = tpost::i($postid);
      if ($post->status != 'published')  {
        return $this->htmlhelper->geterrorcontent($lang->commentondraft);
      }
      
      if (!$post->commentsenabled)       {
        return $this->htmlhelper->geterrorcontent($lang->commentsdisabled);
      }
      
      $header = '';
      if ($post->idperm != 0) {
        $url = litepublisher::$urlmap->url;
        litepublisher::$urlmap->url = $post->url;
        $item = litepublisher::$urlmap->itemrequested;
        litepublisher::$urlmap->itemrequested = dbversion ? litepublisher::$urlmap->getitem($post->idurl) : litepublisher::$urlmap->items[$post->url];
        litepublisher::$urlmap->itemrequested['id'] = $post->idurl;
        $perm = tperm::i($post->idperm);
        $header = $perm->getheader($post);
        // not restore values because perm will be used this values
        //litepublisher::$urlmap->itemrequested = $item;
        //litepublisher::$urlmap->url = $url;
      }
      
      $confirmid  = $kept->add($values);
      return $header . $this->htmlhelper->confirm($confirmid);
    }
    
    $confirmid = $_POST['confirmid'];
    $lang = tlocal::i('comment');
    if (!($values = $kept->getitem($confirmid))) {
      return $this->htmlhelper->geterrorcontent($lang->notfound);
    }
    
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = litepublisher::$classes->posts;
    if(!$posts->itemexists($postid)) {
      return $this->htmlhelper->geterrorcontent($lang->postnotfound);
    }
    
    $post = tpost::i($postid);
    if ($post->status != 'published')  {
      return $this->htmlhelper->geterrorcontent($lang->commentondraft);
    }
    
    $values = array(
    'name' => isset($values['name']) ? tcontentfilter::escape($values['name']) : '',
    'email' => isset($values['email']) ? trim($values['email']) : '',
    'url' => isset($values['url']) ? tcontentfilter::escape(tcontentfilter::clean_website($values['url'])) : '',
    'subscribe' => isset($values['subscribe']),
    'content' => isset($values['content']) ? trim($values['content']) : '',
    'ip' => isset($values['ip']) ? $values['ip'] : '',
    'postid' => $postid,
    'antispam' => isset($values['antispam']) ? $values['antispam'] : ''
    );
    
    $lang = tlocal::i('comment');
    if (!$this->checkspam($values['antispam']))          {
      return $this->htmlhelper->geterrorcontent($lang->spamdetected);
    }
    
    if (empty($values['content'])) return $this->htmlhelper->geterrorcontent($lang->emptycontent);
    if (empty($values['name']))       return $this->htmlhelper->geterrorcontent($lang->emptyname);
    if (!tcontentfilter::ValidateEmail($values['email'])) {
      return $this->htmlhelper->geterrorcontent($lang->invalidemail);
    }
    
    if (!$post->commentsenabled)       {
      return $this->htmlhelper->geterrorcontent($lang->commentsdisabled);
    }
    
    if (litepublisher::$options->checkduplicate) {
      if (litepublisher::$classes->spamfilter->checkduplicate($postid, $values['content']) ) {
        return $this->htmlhelper->geterrorcontent($lang->duplicate);
      }
    }
    
    $posturl = $post->lastcommenturl;
    $users = tcomusers::i($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    if (!litepublisher::$classes->spamfilter->canadd( $uid)) {
      return $this->htmlhelper->geterrorcontent($lang->toomany);
    }
    
    $subscribers = tsubscribers::i();
    $subscribers->update($post->id, $uid, $values['subscribe']);
    
    litepublisher::$classes->commentmanager->addcomment($post->id, $uid, $values['content'], $values['ip']);
    
    $cookies = array();
    $cookie = empty($_COOKIE['userid']) ? '' : $_COOKIE['userid'];
    $usercookie = $users->getcookie($uid);
    if ($usercookie != basemd5($cookie . litepublisher::$secret)) {
      $cookie= md5uniq();
      $usercookie = basemd5($cookie . litepublisher::$secret);
      $users->setvalue($uid, 'cookie', $usercookie);
    }
    $cookies['userid'] = $cookie;
    
    foreach (array('name', 'email', 'url') as $field) {
      $cookies["comuser_$field"] = $values[$field];
    }
    
    if (!dbversion) $cookies['idpost'] = $post->id;
    
    return $this->htmlhelper->sendcookies($cookies, litepublisher::$site->url . $posturl);
  }
  
  private function getconfirmform($confirmid) {
    ttheme::$vars['lang'] = tlocal::i('comment');
    $args = targs::i();
    $args->confirmid = $confirmid;
    $theme = tsimplecontent::gettheme();
    return $theme->parsearg(
    $theme->templates['content.post.templatecomments.confirmform'], $args);
  }
  
  //htmlhelper
  public function confirm($confirmid) {
    return tsimplecontent::html($this->getconfirmform($confirmid));
  }
  
  public function geterrorcontent($s) {
    return tsimplecontent::content($s);
  }
  
  public function sendcookies($cookies, $url) {
    $result = '<?php ';
    foreach ($cookies as $name => $value) {
      $result .= " setcookie('$name', '$value', time() + 30000000,  '/', false);";
    }
    
    $result .= sprintf(" header('Location: %s'); ?>", $url);
    return $result;
  }
  
}//class