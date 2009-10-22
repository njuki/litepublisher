<?php

class TCommentUsers extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'comusers';
    $this->basename = 'commentusers';
    $this->CacheEnabled = false;
  }
  
  public function PostDeleted($postid) {
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
  
  public function add($name, $email, $url) {
    //$name = htmlspecialchars(trim(strip_tags($name)));
    //$url = htmlspecialchars(trim(strip_tags($url)));
    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    if ($id = $this->find($name, $email, $url)) {
      $this->addip($id, $ip);
      return $id;
    }

if (dbversion) {
return $this->db->InsertAssoc(array(
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5(mt_rand() . secret. microtime()),
    ));

} else {
    $this->lock();
    $this->items[++$this->lastid] = array(
    'id' => $this->lastid,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5(mt_rand() . secret. microtime()),
    'ip' => array($ip),
    'subscribe' => array( )
    );
    
    $this->unlock();
    $this->added($this->lastid);
    return $this->lastid;
}
  }
  
  public function edit($id, $name, $url, $email, $ip) {
if (dbversion) {
return $this->UpdateAssoc(array(
'name' => $name,
'email' => $email,
'url' => $url
));
}

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
if (dbversion) return $this->db->findid('cookie = '. dbquote($cookie));

    foreach ($this->items as $id => $item) {
      if ($cookie == $item['cookie']) return $item;
    }
    return false;
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
if (dbversion) {
return $this->db->findid('name = '. dbquote($name) . ' and email = '. dbquote($email) .' and url = '. dbquote($url));
} 

    foreach ($this->items as $id => $item) {
      if (($name == $item['name'])  && ($email == $item['email']) && ($url == $item['url'])) {
        return $id;
      }
    }
    return false;
  }
  
  public function addip($id, $ip) {
if (dbversion) return true;

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
  
  public function getlink($id) {
global $classes, $options;
$item = $this->getitem($id);
    $name = $item['name'];
    $url = $item['url'];
$thisoptions = $this->options;
    if ($thisoptions->hidelink || empty($url) ) return $name;
    
    if (!$classes->commentmanager->HasApprovedCount($id, 2)) return $name;
    
    $rel = $thisoptions->nofollow ? 'rel="nofollow noindex"' : '';
    if ($thisoptions->redir) {
      return "<a $rel href=\"$options->url/comusers/$id/\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
  }
  
  public function request($arg) {
    global $options;
    $id = (int) $arg;
    if (!$this->itemexists($id)) return 404;
$item = $this->getitem($id);
$url = $item['url'];
    if (!strpos($url, '.')) $url = $options->url . $options->home;
    if (substr($url, 0, 7) != 'http://') $url = 'http://' . $url;
    TUrlmap::redir($url);
  }
  
public function DeleteWithoutComments() {
}

}//class

?>