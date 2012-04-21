<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Allow: POST');
      @header('Content-Type: text/plain');
      ?>";
    }
    
    tguard::post();
    if (isset($_POST['confirmid'])) return $this->confirm_recevied();
return $this->processform($_POST, false);
}

public function getshortpost($id) {
$id = (int) $id;
if ($id == 0) return false;
$db = litepublisher::$db;
return $db->selectassoc("select id, idurl, idperm, status, comments_status from $db->posts where id = $id");
}

public function invalidate(array $shortpost) {
    $lang = tlocal::i('comment');
      if(!$shortpost) {
        return $this->htmlhelper->geterrorcontent($lang->postnotfound);
      }
      
      if ($shortpost['status'] != 'published')  {
        return $this->htmlhelper->geterrorcontent($lang->commentondraft);
      }

if ($shortpost['comments_status'] == 'closed') {
        return $this->htmlhelper->geterrorcontent($lang->commentsdisabled);
}

return false;
}

public function processform(array $values, $confirmed) {
    $lang = tlocal::i('comment');
    if (trim($values['content']) == '') return $this->htmlhelper->geterrorcontent($lang->emptycontent);

      $shortpost= $this->getshortpost(isset($values['postid']) ? (int) $values['postid'] : 0);
if ($err = $this->invalidate($shortpost)) return $err;

    $cm = tcommentmanager::i();      
if (litepublisher::$options->ingroups($cm->idgroups)) {
if (!$confirmed && $cm->confirmlogged)  return $this->request_confirm($values, $shortpost);
$iduser = litepublisher::$options->user;
} else {
switch ($shortpost['comments_status']) {
case 'reg':
        return $this->htmlhelper->geterrorcontent($lang->reg);

case 'guest':
if (!$confirmed && $cm->confirmguest)  return $this->request_confirm($values, $shortpost);
$iduser = $cm->idguest;
break;

case 'comuser':
if (!$confirmed && $cm->confirmcomuser)  return $this->request_confirm($values, $shortpost);
$iduser = $this->addcomuser($values);
break;
}
      
}

public function confirm_recevied() {
    $lang = tlocal::i('comment');
/*
    $kept = tkeptcomments::i();
    $kept->deleteold();
*/
    $confirmid = $_POST['confirmid'];
$this->start_session($confirmid);
    //if (!($values = $kept->getitem($confirmid))) {
if (!isset($_SESSION['confirmid'] || ($confirmid != $_SESSION['confirmid'])) {
      return $this->htmlhelper->geterrorcontent($lang->notfound);
    }
$values = $_SESSION['values'];
          session_destroy();
return $this->processform($values, true);
}

  public function start_session($idconfirm) {
    ini_set('session.use_cookies', 0);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.use_only_cookies', 0);
    /*
    if (tfilestorage::$memcache) {
      ini_set('session.save_handler', 'memcache');
      ini_set('session.save_path', 'tcp://127.0.0.1:11211');
    } else {
      ini_set('session.save_handler', 'files');
    }
    */
    
    session_cache_limiter(false);
    session_id ('commentform-' .md5($idconfirm));
    session_start();
  }
  
public function request_confirm(array $values, array $shortpost) {
/*
    $kept = tkeptcomments::i();
    $kept->deleteold();
*/
      $values['date'] = time();
      $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    
      //$confirmid  = $kept->add($values);
$confirmid = md5uniq();
$this->start_session($confirmid);
$_SESSION['confirmid'] = $confirmid;
$_SESSION['values'] = $values;
    session_write_close();

if (intval($shortpost['idperm']) > 0) {
$header = $this->getpermheader($shortpost);
} else {
$header = '';
}

      return $header . $this->htmlhelper->confirm($confirmid);
}

public function getpermheader(array $shortpost) {
$urlmap = litepublisher::$urlmap;
        $url = $urlmap->url;
        $saveitem = $urlmap->itemrequested;
        $urlmap->itemrequested = $urlmap->getitem($shortpost['idurl']);
        $urlmap->url = $urlmap->itemrequested['url'];
        $perm = tperm::i($post->idperm);
        // not restore values because perm will be used this values
return $perm->getheader(tpost::i($shortpost['id']));
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

public function processcomuser($values) {
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
    
$cm = tcommentmanager::i();
    if (litepublisher::$options->checkduplicate) {
      if ($cm->checkduplicate($postid, $values['content']) ) {
        return $this->htmlhelper->geterrorcontent($lang->duplicate);
      }
    }
    
    $posturl = $post->lastcommenturl;
    $users = tcomusers::i($postid);
    $uid = $users->add($values['name'], $values['email'], $values['url'], $values['ip']);
    if (!$cm->canadd( $uid)) {
      return $this->htmlhelper->geterrorcontent($lang->toomany);
    }
    
    $subscribers = tsubscribers::i();
    $subscribers->update($post->id, $uid, $values['subscribe']);
    
$cm->add($post->id, $uid, $values['content'], $values['ip']);
    
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

  public function sendcookies($cookies, $url) {
    $result = '<?php ';
    foreach ($cookies as $name => $value) {
      $result .= " setcookie('$name', '$value', time() + 30000000,  '/', false);";
    }
    
    $result .= sprintf(" header('Location: %s'); ?>", $url);
    return $result;
  }
  
}//class