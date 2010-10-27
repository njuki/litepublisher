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
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'comusers';
    $this->basename = 'comusers';
    $this->cache = false;
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
    
    $id = $this->db->add(array(
    'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'ip' => $ip,
    'cookie' => md5uniq(),
    ));
    
    $manager = tcommentmanager::instance();
    $manager->authoradded($id);
    return $id;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->UpdateAssoc(array(
    'name' => $name,
    'email' => $email,
    'url' => $url,
    'ip' => $ip
    ));
    $manager = tcommentmanager::instance();
    $manager->authoredited($id);
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::instance();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    return $this->db->finditem('cookie = '. dbquote($cookie));
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    $id = $this->db->findid('name = '. dbquote($name) . ' and email = '. dbquote($email) .' and url = '. dbquote($url));
    return $id == '0' ? false : (int) $id;
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if (!$this->itemexists($id)) return turlmap::redir301('/');
    $item = $this->getitem($id);
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . litepublisher::$options->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    turlmap::redir($url);
  }
  
}//class

?>