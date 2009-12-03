<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcomusers extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->dbversion = dbversion;
$this->table = 'comusers';
    $this->basename = 'comusers';
    $this->cache = false;
$this->data['trustlevel'] = 2;
$this->data['hidelink'] = false;
$this->data['redir'] = true;
$this->data['nofollow'] = false;
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
return $this->db->add(array(
'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,

    'cookie' => md5(mt_rand() . secret. microtime()),
    ));

} else {
    $this->lock();
    $this->items[++$this->autoid] = array(
    'id' => $this->autoid,
'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5(mt_rand() . secret. microtime()),
    'ip' => array($ip),
    );
    
    $this->unlock();
    $this->added($this->autoid);
    return $this->autoid;
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
  
  public function fromcookie($cookie) {
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
}  else {
    foreach ($this->items as $id => $item) {
      if (($name == $item['name'])  && ($email == $item['email']) && ($url == $item['url'])) {
        return $id;
      }
    }
    return false;
}
  }
  
  public function addip($id, $ip) {
if (dbversion) return true;

    if (!in_array($ip, $this->items[$id]['ip'])) {
      $this->items[$id]['ip'][] = $ip;
      $this->save();
    }
  }

public function trusted($id) {
$item =$this->getitem($id);
return $this->checktrust($item['trust']);
}

public function checktrust($value) {
return $value >= $this->trustlevel;
}

  public function getidlink($id) {
$item = $this->getitem($id);
return $this->getlink($item['name'], $item['url'], $item['trust']);
}

public function getlink($name, $url, $trust) {
global $options;
    if ($this->hidelink || empty($url) || !$this->checktrust($trust)) return $name;
    $rel = $this->nofollow ? 'rel="nofollow noindex"' : '';
    if ($this->redir) {
      return "<a $rel href=\"$options->url/comusers.htm{$options->q}id=$id\">$name</a>";
    } else {
      return "<a $rel href=\"$url\">$name</a>";
    }
  }
  
  public function request($arg) {
    global $options;
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
try {
$item = $this->getitem($id);
    } catch (Exception $e) {
return 404;
}

$url = $item['url'];
    if (!strpos($url, '.')) $url = $options->url . $options->home;
    if (substr($url, 0, 7) != 'http://') $url = 'http://' . $url;
    TUrlmap::redir($url);
  }
  
}//class

?>