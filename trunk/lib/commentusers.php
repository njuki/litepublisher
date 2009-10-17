<?php

class TCommentUsers extends TItems {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'commentusers';
    $this->basename = 'commentusers';
    $this->CacheEnabled = false;
    $this->Data['hidelink'] = false;
    $this->Data['redir'] = true;
    $this->Data['nofollow'] = false;
  }
  
  public function PostDeleted($postid) {
if (dbversion) return $this->DeleteWithoutComments();

    $this->lock();
$this->DeleteWithoutComments();
    foreach ($this->items as  $id => $item) {
      $i = array_search($postid, $item['subscribe']);
      if (is_int($i)) {
        array_splice($this->items[$id]['subscribe'], $i, 1);
      }
    }
    $this->unlock();
}
  }
  
  public function Add($name, $email, $url) {
    //$name = htmlspecialchars(trim(strip_tags($name)));
    //$url = htmlspecialchars(trim(strip_tags($url)));
    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    if ($id = $this->Find($name, $email, $url)) {
      $this->AddIP($id, $ip);
      return $id;
    }
    $this->lock();
    $this->items[++$this->lastid] = array(
    'id' => $this->lastid,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5(secret. uniqid( microtime())),
    'ip' => array($ip),
    'subscribe' => array( )
    );
    
    $this->unlock();
    $this->Added($this->lastid);
    return $this->lastid;
  }
  
  public function Edit($id, $name, $url, $email, $ip) {
    $this->lock();
    $item = &$this->items[$id];
    $item['name'] = $name;
    $item['url'] = $url;
    $item['email'] = $email;
    if (!in_array($ip, $item['ip'])) {
      $item['ip'][]  = $ip;
    }
    $this->unlock();
    return $id;
  }
  
  public function GetItemFromCookie($cookie) {
    foreach ($this->items as $id => $item) {
      if ($cookie == $item['cookie']) return $item;
    }
    return false;
  }
  
  public function GetCookie($id) {
    return $this->GetValue($id, 'cookie');
  }
  
  public function Find($name, $email, $url) {
    foreach ($this->items as $id => $item) {
      if (($name == $item['name'])  && ($email == $item['email']) && ($url == $item['url'])) {
        return $id;
      }
    }
    return false;
  }
  
  public function AddIP($id, $ip) {
    if (!in_array($ip, $this->items[$id]['ip'])) {
      $this->items[$id]['ip'][] = $ip;
      $this->save();
    }
  }
  
  public function subscribed($id, $postid) {
    return in_array($postid, $this->items[$id]['subscribe']);
  }
  
  public function Subscribe($id, $postid) {
    if (!in_array($postid, $this->items[$id]['subscribe'])) {
      $this->items[$id]['subscribe'][] = $postid;
      $this->save();
    }
  }
  
  public function Unsubscribe($id, $postid) {
    $i = array_search($postid, $this->items[$id]['subscribe']);
    if (is_int($i)) {
      array_splice($this->items[$id]['subscribe'], $i, 1);
      $this->save();
    }
  }
  
  public function UpdateSubscribtion($id, $postid, $subscribed) {
    $postid = (int) $postid;
    if ($subscribed) {
      $this->Subscribe($id, $postid);
    } else {
      $this->Unsubscribe($id, $postid);
    }
  }
  
  public function GetLink($id) {
    if (!isset($this->items[$id])) return '';
    $name = $this->items[$id]['name'];
    $url = $this->items[$id]['url'];
    if ($this->hidelink || empty($url) ) return $name;
    
    $CommentManager = &TCommentManager::Instance();
    if (!$CommentManager->HasApprovedCount($id, 2)) return $name;
    
    $rel = $this->nofollow ? 'rel="nofollow noindex"' : '';
    if ($this->redir) {
      global $Options;
      return "<a $rel href=\"$Options->url/authors/$id/\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
  }
  
  public function Request($arg) {
    global $Options;
    $id = (int) $arg;
    if (!isset($this->items[$id])) return 404;
    $url = $this->items[$id]['url'];
    if (!strpos($url, '.')) $url = $Options->url . $Options->home;
    if (substr($url, 0, 7) != 'http://') $url = 'http://' . $url;
    TUrlmap::redir($url);
  }
  

public function DeleteWithoutComments() {
if (dbversion) {
$comments = tcomments(::instance();
$this->db->delete("id not in (select author from $comments->thistable group by author)");
} else {
}
}

}//class

?>