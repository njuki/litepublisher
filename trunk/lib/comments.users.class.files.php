<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcomusers extends titems {
  public $pid;
  private static $instances;
  
  public static function instance($pid) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
    $self = litepublisher::$classes->newinstance(__class__);
    self::$instances[$pid]  = $self;
    $self->pid = $pid;
    $self->load();
    return $self;
  }
  
  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments.authors';
  }
  
  public function add($name, $email, $url, $ip) {
    if ($id = $this->find($name, $email, $url)) return $id;
    
    if (($parsed = @parse_url($url)) &&  is_array($parsed) ) {
      if ( empty($parsed['host'])) {
        $url = '';
      } else {
        if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) {
          $parsed['scheme']= 'http';
        }
        $url = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
        if (!empty($parsed['query'])) $url .= '?' . $parsed['query'];
      }
    } else {
      $url = '';
    }
    
    $this->lock();
    $this->items[++$this->autoid] = array(
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5uniq(),
    'ip' => $ip
    );
    
    $this->unlock();
    $manager = tcommentmanager::instance();
    $manager->authoradded($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->lock();
    $item = &$this->items[$id];
    $item['name'] = $name;
    $item['url'] = $url;
    $item['email'] = $email;
    $item['ip'] = $ip;
    $this->unlock();
    $manager = tcommentmanager::instance();
    $manager->authoredited($id);
    return $id;
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::instance();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    foreach ($this->items as $id => $item) {
      if ($cookie == $item['cookie']) {
        $item['id'] = $id;
        return $item;
      }
    }
    return false;
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    foreach ($this->items as $id => $item) {
      if (($name == $item['name'])  && ($email == $item['email']) && ($url == $item['url'])) {
        return $id;
      }
    }
    return false;
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    $idpost = isset($_GET['post']) ? (int) $_GET['post'] : 1;
    if ($idpost != $this->pid) {
      $this->pid = $idpost;
      $this->load();
    }
    
    try {
      $item = $this->getitem($id);
    } catch (Exception $e) {
      return turlmap::redir301('/');
    }
    
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . $options->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    turlmap::redir($url);
  }
  
}//class

?>