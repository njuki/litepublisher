<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/
if (!class_exists('tkeptcomments', false)) {
  if (dbversion) {
    class tkeptcomments extends tdata {
      
      public static function instance() {
        return getinstance(__class__);
      }
      
      protected function create() {
        parent::create();
        $this->table ='commentskept';
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
    
  } else {
    
    class tkeptcomments extends titems {
      
      public static function instance() {
        return getinstance(__class__);
      }
      
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
        $confirmid = md5uniq();
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
  
  public static function getcomuser($postid) {
    if (!empty($_COOKIE['userid'])) {
      $cookie = basemd5($_COOKIE['userid']  . litepublisher::$secret);
      $comusers = tcomusers::instance($postid);
      $user = $comusers->fromcookie($cookie);
      $comusers->loadall();
      if (!dbversion && !$user && !empty($_COOKIE["idpost"])) {
        $comusers2 = tcomusers::instance( (int) $_COOKIE['idpost']);
        $user = $comusers2->fromcookie($cookie);
      }
      return $user;
    }
    return false;
  }
  
  public static function printform($postid, $themename) {
    $result = '';
    $self = self::instance();
    $lang = tlocal::instance('comment');
    $theme = ttheme::getinstance($themename);
    $args = targs::instance();
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
      $subscribers = tsubscribers::instance();
      $args->subscribe = $subscribers->subscribed($postid, $user['id']);
      
      $comments = tcomments::instance($postid);
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
    
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    
    $kept = new tkeptcomments();
    if (!isset($_POST['confirmid'])) {
      $values = $_POST;
      $values['date'] = time();
      $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
      $confirmid  = $kept->add($values);
      return tsimplecontent::html($this->getconfirmform($confirmid));
    }
    
    $confirmid = $_POST['confirmid'];
    if (!($values = $kept->getitem($confirmid))) {
      return tsimplecontent::content(tlocal::$data['commentform']['notfound']);
    }
    $postid = isset($values['postid']) ? (int) $values['postid'] : 0;
    $posts = litepublisher::$classes->posts;
    if(!$posts->itemexists($postid)) return tsimplecontent::content(tlocal::$data['default']['postnotfound']);
    $post = tpost::instance($postid);
    
    $values = array(
    'name' => isset($values['name']) ? tcontentfilter::escape($values['name']) : '',
    'email' => isset($values['email']) ? trim($values['email']) : '',
    'url' => isset($values['url']) ? tcontentfilter::escape($values['url']) : '',
    'subscribe' => isset($values['subscribe']),
    'content' => isset($values['content']) ? trim($values['content']) : '',
    'ip' => isset($values['ip']) ? $values['ip'] : '',
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
    if (litepublisher::$options->checkduplicate) {
      if (litepublisher::$classes->spamfilter->checkduplicate($postid, $values['content']) ) return tsimplecontent::content($lang->duplicate);
    }
    
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
    $users = tcomusers::instance($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    if (!litepublisher::$classes->spamfilter->canadd( $uid)) return tsimplecontent::content($lang->toomany);
    
    $subscribers = tsubscribers::instance();
    $subscribers->update($post->id, $uid, $values['subscribe']);
    
    litepublisher::$classes->commentmanager->addcomment($post->id, $uid, $values['content'], $values['ip']);
    $result = '<?php ';
    
    if (empty($_COOKIE['userid'])) {
      $cookie = '';
    } else {
      $cookie = basemd5($_COOKIE['userid']  . litepublisher::$secret);
    }
    
    $usercookie = $users->getcookie($uid);
    if ($cookie != $usercookie) {
      $cookie= md5uniq();
      $usercookie = basemd5($cookie . litepublisher::$secret);
      $users->setvalue($uid, 'cookie', $usercookie);
      $result .= " setcookie('userid', '$cookie', time() + 30000000,  '/', false);";
    }

foreach (array('name', 'email', 'url') as $field) {
$fieldvalue = $values[$field];
$result .= " setcookie('comuser_$field', '$fieldvalue', time() + 30000000,  '/', false);";
}
    
    if (!dbversion) $result .= " @setcookie('idpost', '$post->id', time() + 30000000,  '/', false);";
    $result .= sprintf(" header('Location: %s%s'); ?>", litepublisher::$site->url,  $posturl);
    return $result;
  }
  
  private function getconfirmform($confirmid) {
    ttheme::$vars['lang'] = tlocal::instance($this->basename);
    $args = targs::instance();
    $args->confirmid = $confirmid;
    $theme = tsimplecontent::gettheme();
    return $theme->parsearg(
    $theme->templates['content.post.templatecomments.confirmform'], $args);
    //$theme->content->post->templatecomments->confirmform, $args);
  }
  
}//class

?>